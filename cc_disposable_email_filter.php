<?php
/**
 * PrestaShop module to block disposable email addresses
 *
 * @author    Cesar Cardinale
 * @copyright Copyright (c) 2024
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Cc_Disposable_Email_Filter extends Module
{
    const BLOCKLIST_URL = 'https://raw.githubusercontent.com/disposable-email-domains/disposable-email-domains/main/disposable_email_blocklist.conf';
    const BLOCKLIST_CACHE_FILE = 'blocklist_cache.txt';
    const BLOCKLIST_CACHE_DURATION = 86400; // 24 hours

    public function __construct()
    {
        $this->name = 'cc_disposable_email_filter';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Cesar Cardinale';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '9.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Disposable Email Filter', [], 'Modules.CcDisposableEmailFilter.Admin');
        $this->description = $this->trans('Block customer registration with disposable email addresses and log attempts.', [], 'Modules.CcDisposableEmailFilter.Admin');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module?', [], 'Modules.CcDisposableEmailFilter.Admin');
    }

    /**
     * Install the module
     */
    public function install()
    {
        return parent::install()
            && $this->registerHook('actionSubmitAccountBefore')
            && $this->createTables()
            && Configuration::updateValue('CC_DEF_ENABLE', 1)
            && Configuration::updateValue('CC_DEF_AUTO_UPDATE', 1);
    }

    /**
     * Uninstall the module
     */
    public function uninstall()
    {
        return parent::uninstall()
            && $this->deleteTables()
            && Configuration::deleteByName('CC_DEF_ENABLE')
            && Configuration::deleteByName('CC_DEF_AUTO_UPDATE');
    }

    /**
     * Create database tables
     */
    private function createTables()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'cc_disposable_email_log` (
            `id_log` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(255) NOT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `date_add` datetime NOT NULL,
            PRIMARY KEY (`id_log`),
            KEY `email` (`email`),
            KEY `date_add` (`date_add`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Delete database tables
     */
    private function deleteTables()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'cc_disposable_email_log`';
        return Db::getInstance()->execute($sql);
    }

    /**
     * Hook before customer creation
     */
    public function hookActionSubmitAccountBefore($params)
    {
        if (!Configuration::get('CC_DEF_ENABLE')) {
            return true;
        }

        if (isset($params['newCustomer']) && isset($params['newCustomer']['email'])) {
            $email = $params['newCustomer']['email'];
        }

        if(empty($email)) {
            $email = Tools::getValue('email');
        }

        if(!empty($email)){
            if ($this->isDisposableEmail($email)) {
                $this->logBlockedAttempt($email);
                Context::getContext()->controller->errors[] = $this->trans('Registration with disposable email addresses is not allowed.', [], 'Modules.CcDisposableEmailFilter.Admin');
                
                throw new PrestaShopException($this->trans('Registration with disposable email addresses is not allowed.', [], 'Modules.CcDisposableEmailFilter.Admin'));
            }
        }

        return true;
    }

    /**
     * Check if email is from a disposable domain
     */
    private function isDisposableEmail($email)
    {
        $domain = $this->extractDomain($email);
        if (!$domain) {
            return false;
        }

        $blocklist = $this->getBlocklist();
        return in_array(strtolower($domain), $blocklist);
    }

    /**
     * Extract domain from email address
     */
    private function extractDomain($email)
    {
        $parts = explode('@', $email);
        return isset($parts[1]) ? strtolower($parts[1]) : null;
    }

    /**
     * Get blocklist from cache or remote source
     */
    private function getBlocklist()
    {
        $cacheFile = $this->getLocalPath() . self::BLOCKLIST_CACHE_FILE;
        
        // Check if cache exists and is still valid
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < self::BLOCKLIST_CACHE_DURATION) {
            $content = file_get_contents($cacheFile);
            return array_filter(explode("\n", $content));
        }

        // Fetch from remote source
        $blocklist = $this->fetchBlocklist();
        
        // Cache the blocklist
        if (!empty($blocklist)) {
            file_put_contents($cacheFile, implode("\n", $blocklist));
        }

        return $blocklist;
    }

    /**
     * Fetch blocklist from remote URL
     */
    private function fetchBlocklist()
    {
        $content = Tools::file_get_contents(self::BLOCKLIST_URL);
        
        if ($content === false) {
            // If fetch fails, try to use cached version even if expired
            $cacheFile = $this->getLocalPath() . self::BLOCKLIST_CACHE_FILE;
            if (file_exists($cacheFile)) {
                $content = file_get_contents($cacheFile);
            } else {
                return [];
            }
        }

        // Parse the content - each line is a domain
        $domains = array_filter(array_map('trim', explode("\n", $content)));
        $domains = array_map('strtolower', $domains);
        
        return $domains;
    }

    /**
     * Log blocked registration attempt
     */
    private function logBlockedAttempt($email)
    {
        $ip = Tools::getRemoteAddr();
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'cc_disposable_email_log` 
                (`email`, `ip_address`, `user_agent`, `date_add`) 
                VALUES (
                    "' . pSQL($email) . '",
                    "' . pSQL($ip) . '",
                    "' . pSQL($userAgent) . '",
                    NOW()
                )';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Get blocked attempts log
     */
    public function getBlockedAttempts($limit = 100, $offset = 0)
    {
        $sql = 'SELECT `id_log`, `email`, `ip_address`, `date_add` 
                FROM `' . _DB_PREFIX_ . 'cc_disposable_email_log` 
                ORDER BY `date_add` DESC 
                LIMIT ' . (int)$offset . ', ' . (int)$limit;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get total count of blocked attempts
     */
    public function getTotalBlockedAttempts()
    {
        $sql = 'SELECT COUNT(*) as total FROM `' . _DB_PREFIX_ . 'cc_disposable_email_log`';
        $result = Db::getInstance()->getRow($sql);
        return isset($result['total']) ? (int)$result['total'] : 0;
    }

    /**
     * Clear blocklist cache
     */
    public function clearCache()
    {
        $cacheFile = $this->getLocalPath() . self::BLOCKLIST_CACHE_FILE;
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }
        return true;
    }

    /**
     * Module configuration page
     */
    public function getContent()
    {
        $output = '';

        // Process form submission
        if (Tools::isSubmit('submit' . $this->name)) {
            Configuration::updateValue('CC_DEF_ENABLE', Tools::getValue('CC_DEF_ENABLE'));
            Configuration::updateValue('CC_DEF_AUTO_UPDATE', Tools::getValue('CC_DEF_AUTO_UPDATE'));
            $output .= $this->displayConfirmation($this->trans('Settings updated successfully.', [], 'Modules.CcDisposableEmailFilter.Admin'));
        }

        // Process cache clear
        if (Tools::isSubmit('clear_cache')) {
            if ($this->clearCache()) {
                $output .= $this->displayConfirmation($this->trans('Cache cleared successfully.', [], 'Modules.CcDisposableEmailFilter.Admin'));
            } else {
                $output .= $this->displayError($this->trans('Failed to clear cache.', [], 'Modules.CcDisposableEmailFilter.Admin'));
            }
        }

        // Display configuration form
        $output .= $this->renderForm();
        $output .= $this->renderStats();
        $output .= $this->renderLogTable();

        return $output;
    }

    /**
     * Render configuration form
     */
    private function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Settings', [], 'Modules.CcDisposableEmailFilter.Admin'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Enable filter', [], 'Modules.CcDisposableEmailFilter.Admin'),
                        'name' => 'CC_DEF_ENABLE',
                        'is_bool' => true,
                        'desc' => $this->trans('Enable or disable the disposable email filter.', [], 'Modules.CcDisposableEmailFilter.Admin'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Enabled', [], 'Modules.CcDisposableEmailFilter.Admin')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('Disabled', [], 'Modules.CcDisposableEmailFilter.Admin')
                            ]
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Auto-update blocklist', [], 'Modules.CcDisposableEmailFilter.Admin'),
                        'name' => 'CC_DEF_AUTO_UPDATE',
                        'is_bool' => true,
                        'desc' => $this->trans('Automatically update the blocklist daily.', [], 'Modules.CcDisposableEmailFilter.Admin'),
                        'values' => [
                            [
                                'id' => 'update_on',
                                'value' => 1,
                                'label' => $this->trans('Enabled', [], 'Modules.CcDisposableEmailFilter.Admin')
                            ],
                            [
                                'id' => 'update_off',
                                'value' => 0,
                                'label' => $this->trans('Disabled', [], 'Modules.CcDisposableEmailFilter.Admin')
                            ]
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Modules.CcDisposableEmailFilter.Admin'),
                    'class' => 'btn btn-default pull-right'
                ],
                'buttons' => [
                    [
                        'type' => 'submit',
                        'title' => $this->trans('Clear Cache', [], 'Modules.CcDisposableEmailFilter.Admin'),
                        'icon' => 'process-icon-refresh',
                        'name' => 'clear_cache',
                        'class' => 'btn btn-default'
                    ]
                ]
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? 
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => [
                'CC_DEF_ENABLE' => Configuration::get('CC_DEF_ENABLE'),
                'CC_DEF_AUTO_UPDATE' => Configuration::get('CC_DEF_AUTO_UPDATE'),
            ],
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        ];

        return $helper->generateForm([$fields_form]);
    }

    /**
     * Render statistics
     */
    private function renderStats()
    {
        $total = $this->getTotalBlockedAttempts();
        $cacheFile = $this->getLocalPath() . self::BLOCKLIST_CACHE_FILE;
        $cacheAge = file_exists($cacheFile) ? 
            human_time_diff(filemtime($cacheFile), time()) : $this->trans('Never updated', [], 'Modules.CcDisposableEmailFilter.Admin');
        
        $blocklist = $this->getBlocklist();
        $blocklistCount = count($blocklist);

        $html = '
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-bar-chart"></i> ' . $this->trans('Statistics', [], 'Modules.CcDisposableEmailFilter.Admin') . '
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="well">
                            <h3>' . $total . '</h3>
                            <p>' . $this->trans('Total blocked attempts', [], 'Modules.CcDisposableEmailFilter.Admin') . '</p>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="well">
                            <h3>' . $blocklistCount . '</h3>
                            <p>' . $this->trans('Domains in blocklist', [], 'Modules.CcDisposableEmailFilter.Admin') . '</p>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="well">
                            <h3>' . $cacheAge . '</h3>
                            <p>' . $this->trans('Cache age', [], 'Modules.CcDisposableEmailFilter.Admin') . '</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

        return $html;
    }

    /**
     * Render log table
     */
    private function renderLogTable()
    {
        $logs = $this->getBlockedAttempts(50);

        $html = '
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-list"></i> ' . $this->trans('Recent Blocked Attempts', [], 'Modules.CcDisposableEmailFilter.Admin') . '
            </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>' . $this->trans('Email', [], 'Modules.CcDisposableEmailFilter.Admin') . '</th>
                            <th>' . $this->trans('IP Address', [], 'Modules.CcDisposableEmailFilter.Admin') . '</th>
                            <th>' . $this->trans('Date', [], 'Modules.CcDisposableEmailFilter.Admin') . '</th>
                        </tr>
                    </thead>
                    <tbody>';

        if (empty($logs)) {
            $html .= '
                        <tr>
                            <td colspan="3" class="text-center">' . 
                                $this->trans('No blocked attempts yet.', [], 'Modules.CcDisposableEmailFilter.Admin') . 
                            '</td>
                        </tr>';
        } else {
            foreach ($logs as $log) {
                $html .= '
                        <tr>
                            <td>' . htmlspecialchars($log['email']) . '</td>
                            <td>' . htmlspecialchars($log['ip_address']) . '</td>
                            <td>' . $log['date_add'] . '</td>
                        </tr>';
            }
        }

        $html .= '
                    </tbody>
                </table>
            </div>
        </div>';

        return $html;
    }
}

/**
 * Helper function for human-readable time difference
 */
if (!function_exists('human_time_diff')) {
    function human_time_diff($from, $to = 0)
    {
        if (empty($to)) {
            $to = time();
        }

        $diff = abs($to - $from);

        if ($diff < 60) {
            $unit = ($diff == 1) ? 'second' : 'seconds';
            return sprintf('%s %s', $diff, $unit);
        }

        $mins = round($diff / 60);
        if ($mins < 60) {
            $unit = ($mins == 1) ? 'minute' : 'minutes';
            return sprintf('%s %s', $mins, $unit);
        }

        $hours = round($diff / 3600);
        if ($hours < 24) {
            $unit = ($hours == 1) ? 'hour' : 'hours';
            return sprintf('%s %s', $hours, $unit);
        }

        $days = round($diff / 86400);
        $unit = ($days == 1) ? 'day' : 'days';
        return sprintf('%s %s', $days, $unit);
    }
}
