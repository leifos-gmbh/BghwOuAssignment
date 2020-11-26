<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * LDAP role assignment by ou assignment
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilBghwOuAssignmentPlugin extends ilLDAPPlugin implements ilLDAPRoleAssignmentPlugin
{
    /**
     * @var string
     */
    const PLUGIN_NAME = 'BghwOuAssignment';

    const PLUGIN_ID_PRAEVENTION = 1;
    const PLUGIN_ID_PRODUKTION = 2;
    const PLUGIN_ID_ZENTRALE_DIENSTE = 3;
    const PLUGIN_ID_IT = 4;
    const PLUGIN_ID_DIREKTION = 5;
    const PLUGIN_ID_UK = 6;

    const PLUGIN_ID_BGHW = 7;

    const PLUGIN_OU_ASSIGNMENTS = [
        self::PLUGIN_ID_PRAEVENTION => [
            'Praevention'
        ],
        self::PLUGIN_ID_PRODUKTION => [
            'MuB',
            'Rul'
        ],
        self::PLUGIN_ID_ZENTRALE_DIENSTE => [
            'BuS',
            'Personal',
            'Finanzen',
            'Regress',
            'Controlling',
            'Justiziariat'
        ],
        self::PLUGIN_ID_IT => [
            'IT'
        ],
        self::PLUGIN_ID_DIREKTION => [
            'Direktion',
            'Geschaeftsfuehrung',
            'Revision und Integrität',
            'Innenrevision'
        ],
        self::PLUGIN_ID_UK => [
            'Unternehmenskommunikation'
        ]
    ];

    /**
     * @var ilLogger
     */
    protected $logger;

    /**
     *  Init slot
     */
    public function slotInit()
    {
        global $DIC;

        $this->logger = $DIC->logger()->auth();
    }

    /**
     * @inheritDoc
     */
    function getPluginName()
    {
        return self::PLUGIN_NAME;
    }

    /**
     * @inheritDoc
     */
    public function checkRoleAssignment($a_plugin_id, $a_user_data)
    {
        $this->logger->dump($a_user_data,  ilLogLevel::DEBUG);
        if (!array_key_exists($a_plugin_id, self::PLUGIN_OU_ASSIGNMENTS)) {
            $this->logger->error('Unknown plugin id configure in LDAP role assignment');
            return false;
        }
        if (!isset($a_user_data['dn'])) {
            $this->logger->warning('No dn provided');
            return false;
        }
        return $this->matches((int) $a_plugin_id,(string) $a_user_data['dn']);
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalAttributeNames()
    {
        return [];
    }

    /**
     * @param int    $plugin_id
     * @param string $dn
     * @return bool
     */
    private function matches(int $plugin_id, string $dn)  : bool
    {
        if ($plugin_id == self::PLUGIN_ID_BGHW) {
            return true;
        }

        $this->logger->debug('Comparing with dn = ' . $dn);
        foreach (self::PLUGIN_OU_ASSIGNMENTS[$plugin_id] as $ou) {
            $this->logger->debug('Comparing with ' . $ou);
            if (stripos($dn, 'OU=' . $ou . ',') !== false) {
                $this->logger->debug('... matches');
                return true;
            }
        }
        return false;
    }
}