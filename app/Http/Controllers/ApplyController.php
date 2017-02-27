<?php namespace App\Http\Controllers;
use Illuminate\Support\Facades\Input;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Log;
use Validator;
use Redirect;
use Request;
use Session;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;
class ApplyController extends Controller {
    public function upload() {
        // getting all of the post data
        $file = array('products' => Input::file('products'),'template' => Input::file('template'));
        // setting up rules
        $rules = array('products' => 'required','template' => 'required'); //mimes:jpeg,bmp,png and for max size max:10000
        // doing the validation, passing post data, rules and the messages
        $validator = Validator::make($file, $rules);
        if ($validator->fails()) {
            // send back to the page with the input data and errors
            return Redirect::to('create')->withInput()->withErrors($validator);
        }
        else {
            // checking file is valid.
            if (Input::file('products')->isValid() && Input::file('template')->isValid()) {

                $productsExtension = Input::file('products')->getClientOriginalExtension(); // getting products excel extension
                $templateExtension = Input::file('template')->getClientOriginalExtension(); // getting products excel extension

                if($productsExtension == "xlsx" && $templateExtension == "label"){
                    try
                    {
                        /*
                        * Read Excel and create files
                        */
                        $templateContent = File::get($file['template']);
                        $productsContent =  Excel::load($file['products'], function($reader) {
                        })->get();

                        Log::alert($productsContent);

                        // create new label for each product in excel
                        foreach ($productsContent as $product){
                            $templateContent = File::get($file['template']);
                            Log::alert("product" . $product);
                            //Replace content

                            //Break ingredienten after x chars
                            $ingredienten = chunk_split($product->ingredienten, 33, "\n");
                            $ingredienten = $ingredienten . "\n" . "*Van biologische oorsprong";

                            $templateContent = str_replace("%naam%",$product->naam ,$templateContent);
                            $templateContent = str_replace("%ingredienten%",$ingredienten ,$templateContent);
                            $templateContent = str_replace("%gebruik%",$product->gebruik ,$templateContent);
                            $templateContent = str_replace("%bewaar%",$product->bewaar ,$templateContent);
                            $templateContent = str_replace("%gram%",$product->gram ,$templateContent);

                            //Create new label
                            $exportFolder = storage_path('app/file/');
                            $bytes_written = File::put($exportFolder . $product->naam . ".label", $templateContent);
                            if ($bytes_written === false) {
                                Session::flash('error', 'cannot create new label file');
                                return Redirect::to('create');
                            }
                            Log::alert("File created: " . $product->naam . ".label");
                        }
                        //ZIP and download
                        $zip = new ZipArchive();
                        $zip_name = time().".zip"; // Zip name
                        $zip->open($zip_name,  ZipArchive::CREATE);

                        foreach (glob(storage_path('app/file/').'/*.*') as $file ) {
                            if(file_exists($file)){
                                $zip->addFromString(basename($file),  file_get_contents($file));
                            }
                            else{
                                echo"file does not exist";
                            }
                        }
                        $zip->close();
                        return response()->download($zip_name);
                    }
                    catch (FileNotFoundException $exception)
                    {
                        Session::flash('error', 'uploaded file is not valid, only .xlsx and .label');
                        return Redirect::to('create');
                    }
                    Session::flash('success', 'Labels generated succesfully');
                    return Redirect::to('create');
                } else {
                    Session::flash('error', 'uploaded file is not valid, only .xlsx and .label');
                    return Redirect::to('create');
                }
            }
            else {
                // sending back with error message.
                Session::flash('error', 'uploaded file is not valid');
                return Redirect::to('create');
            }
        }

    }
    public function zipFiles(){
        $zip = new ZipArchive();
        $zip_name = time().".zip"; // Zip name
        $zip->open($zip_name,  ZipArchive::CREATE);

        foreach (glob(storage_path('app/file/').'/*.*') as $file ) {
            if(file_exists($file)){
                $zip->addFromString(basename($file),  file_get_contents($file));
            }
            else{
                echo"file does not exist";
            }
        }
        $zip->close();
        return $zip_name;
    }
}