<?php

namespace SLiMS;

use Throwable;
use SLiMS\Http\Request;
use SLiMS\Json;
use Spatie\Ignition\Ignition as SpatieIgnition;
use Spatie\FlareClient\Report;

class Ignition extends SpatieIgnition
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function init()
    {
        $self = new self(); 
        return config('ignition') !== null ? $self->useDarkMode() : $self;
    }

    public function SlimsHandleException(Throwable $throwable): Report
    {
        $this->setUpFlare();

        $report = $this->createReport($throwable);
        $request = Request::capture();

        if ($this->shouldDisplayException && $this->inProductionEnvironment !== true) {
            if ($request->headers('Content-Type') === 'application/json') $this->renderExceptionAsJson($report);
            else $this->renderException($throwable, $report);
        }

        if ($this->flare->apiTokenSet() && $this->inProductionEnvironment !== false) {
            $this->flare->report($throwable, report: $report);
        }

        return $report;
    }

    public function renderExceptionAsJson(Report $report)
    {
        response()->json([
            'status' => false,
            'message' => $report->getMessage(),
            'exception' => $report->getExceptionClass(),
            'stacktrace' => $report->toArray()['stacktrace']??[]
        ])->send();
    }

    public function register(): self
    {
        error_reporting(-1);

        /** @phpstan-ignore-next-line  */
        set_error_handler([$this, 'renderError']);

        /** @phpstan-ignore-next-line  */
        set_exception_handler([$this, 'SlimsHandleException']);

        return $this;
    }
}