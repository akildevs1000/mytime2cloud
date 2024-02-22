<?php

namespace App\Jobs;

use App\Http\Controllers\Controller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsPDFGeneratorJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $folder_name;
    protected $blade_name;
    protected $data;
    protected $system_user_id;



    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($folder_name, $blade_name, $data, $system_user_id)
    {
        $this->folder_name = $folder_name;
        $this->blade_name = $blade_name;
        $this->data = $data;
        $this->system_user_id = $system_user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {


            $file_path =   "temp_pdf/" . $this->folder_name . "/" . $this->system_user_id . ".pdf";
            $data_pdf = Pdf::loadView($this->blade_name,  $this->data)->output();
            Storage::disk('public')->put($file_path, $data_pdf);
            $data_pdf = null;
            $file_name_raw = "jobs_pdf/jobs_pdf_" . date("d-m-Y") . ".txt";
            Storage::append($file_name_raw, json_encode($file_path) . ' - Generated Successfully');
            echo  $file_path;
        } catch (\Exception $e) {
            $file_name_raw = "jobs_pdf/jobs_pdf_error_" . date("d-m-Y") . ".txt";
            Storage::append($file_name_raw,  $file_path  . ' - ' . $e->getMessage());

            echo   $e->getMessage();
        }
    }
}
