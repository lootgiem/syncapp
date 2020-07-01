<?php


namespace App\Services\Synchronizer;


use LogicException;

trait Reportable
{
    public function addReportSection($title, $content)
    {
        if (property_exists($this, 'report')) {
            $this->report->put($title, $content);
        }
        else {
            throw new LogicException('You have to add a $report attribute to use the reportable trait.');
        }
    }

    public function addReportContent($content)
    {
        $this->report->add($content);
    }

    public function getReport()
    {
        if (property_exists($this, 'report')) {
            return $this->report;
        }
        else {
            throw new LogicException('You have to add a $report attribute to use the reportable trait.');
        }
    }
}
