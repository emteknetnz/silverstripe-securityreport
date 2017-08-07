<?php
namespace SilverStripe\SecurityReport;

use SilverStripe\Control\Director;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Reports\Report;

/**
 * User Security Report
 *
 * @author Michael Parkhill <mike@silverstripe.com>
 * @package securityreport
 */
class UserSecurityReport extends Report
{

    /**
     * Columns in the report
     *
     * @var array
     * @config
     */
    private static $columns = array(
        'ID' => 'User ID',
        'FirstName' => 'First Name',
        'Surname' => 'Surname',
        'Email' => 'Email',
        'Created' => 'Date Created',
        'GroupsDescription' => 'Groups',
        'PermissionsDescription' => 'Permissions'
    );
    
    protected $dataClass = 'Member';

    /**
     * Returns the report title
     *
     * @return string
     */
    public function title()
    {
        return _t('UserSecurityReport.REPORTTITLE', 'Users, Groups and Permissions');
    }

    /**
     * Builds a report description which is the current hostname with the current date and time
     *
     * @return string e.g. localhost/sitename - 21/12/2112
     */
    public function description()
    {
        return str_replace(
            array('http', 'https', '://'),
            '',
            Director::protocolAndHost() . ' - ' . date('d/m/Y H:i:s')
        );
    }

    /**
     * Returns the column names of the report
     *
     * @return array
     */
    public function columns()
    {
        return self::config()->columns;
    }

    /**
     * Alias of columns(), to support the export to csv action
     * in {@link GridFieldExportButton} generateExportFileData method.
     * @return array
     */
    public function getColumns()
    {
        return $this->columns();
    }

    /**
     * @return array
     */
    public function summaryFields()
    {
        return $this->columns();
    }

    /**
     * Defines the sortable columns on the report gridfield
     *
     * @return array
     */
    public function sortColumns()
    {
        return array_keys($this->columns());
    }

    /**
     * Get the source records for the report gridfield
     *
     * @return DataList
     */
    public function sourceRecords()
    {
        // Get members sorted by ID
        return Member::get()->sort('ID');
    }

    /**
     * Restrict access to this report to users with security admin access
     *
     * @param Member $member
     * @return boolean
     */
    public function canView($member = null)
    {
        return (bool)Permission::checkMember($member, "CMS_ACCESS_SecurityAdmin");
    }

    /**
     * Return a field, such as a {@link GridField} that is
     * used to show and manipulate data relating to this report.
     *
     * @return FormField subclass
     */
    public function getReportField()
    {
        $gridField = parent::getReportField();
        $gridField->setModelClass(self::class);
        $gridConfig = $gridField->getConfig();
        $gridConfig->removeComponentsByType([GridFieldPrintButton::class, GridFieldExportButton::class]);
        $gridConfig->addComponents(
            new GridFieldPrintReportButton('buttons-before-left'),
            new GridFieldExportReportButton('buttons-before-left')
        );
        return $gridField;
    }
}
