<?php

// namespace App\helpers;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Spatie\PdfToImage\Pdf;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

// class MyPDF extends \TCPDF
// {
//     // Override the Header method with an empty implementation
//     public function Header()
//     {
//         // Do nothing, effectively removing the header
//     }
// }

function storeImage2($requestKey, $i = 1, $upload_folder = "image"){
    $file_path1 = "";
    $file_input = $requestKey;

    if ($file_input != "") {
        if (preg_match('/^data:image\/([a-zA-Z0-9\+\-\.]+);base64,/', $file_input, $type)) {
            $file_input = substr($file_input, strpos($file_input, ',') + 1);
            $type = strtolower($type[1]);
            $type = str_replace('svg+xml', 'svg', $type);
            $file_input = base64_decode($file_input);

            if ($file_input !== false) {
                $file_path1 = $upload_folder . "/" . date("Y") . "/" . date("m") . "/" . $i . "-" . time() . "." . $type;
                $directory = dirname($file_path1);
                $fullPublicPath = public_path('storage/' . $directory);

                // Ensure the directory exists
                if (!file_exists($fullPublicPath)) {
                    mkdir($fullPublicPath, 0755, true);
                }

                // Save the decoded image data to file
                file_put_contents($fullPublicPath . '/' . basename($file_path1), $file_input);

                // Return the public path
                $file_path1 = 'public/storage/' . $file_path1;

                /*
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory, 0755, true, true);

                    $fullPublicPath = public_path('storage/' . $directory);
                    if (file_exists($fullPublicPath)) {
                        chmod($fullPublicPath, 0755);
                    }
                }
                
                $path = Storage::disk('')->put($file_path1, $file_input);
                $file_path1 = "public/storage/" . $file_path1;
                */
                return $file_path1;
            }
        } else {
            return $file_input;
        }
    }

    return null;
}

function storeImage($requestKey, $i = 1, $upload_folder = "image"){
    $file_path1 = "";
    $file_input = $requestKey;

    if ($file_input != "") {
        if (preg_match('/^data:image\/([a-zA-Z0-9\+\-\.]+);base64,/', $file_input, $type)) {
            $file_input = substr($file_input, strpos($file_input, ',') + 1);
            $type = strtolower($type[1]);
            $type = str_replace('svg+xml', 'svg', $type);
            $file_input = base64_decode($file_input);

            if ($file_input !== false) {
                $file_path1 = $upload_folder . "/" . date("Y") . "/" . date("m") . "/" . $i . "-" . time() . "." . $type;
                $directory = dirname($file_path1);

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory, 0755, true, true);

                    $fullPublicPath = public_path('storage/' . $directory);
                    if (file_exists($fullPublicPath)) {
                        chmod($fullPublicPath, 0755);
                    }
                }

                $path = Storage::disk('public')->put($file_path1, $file_input);
                $file_path1 = "public/storage/" . $file_path1;

                return $file_path1;
            }
        } else {
            return $file_input;
        }
    }

    return null;
}

function storeFiles($file_input, $i = 1, $upload_folder = "image", $maxSize = 10240)
{
    if (!$file_input) {
        return null;
    }

    $allowedMimes = [
        'jpg','jpeg','png','gif','bmp','webp','svg',
        'pdf','doc','docx','txt','rtf',
        'xls','xlsx','csv',
        'ppt','pptx',
        'zip','rar'
    ];

    $extension = strtolower($file_input->getClientOriginalExtension());

    if (!in_array($extension, $allowedMimes)) {
        throw new \Exception('File type not allowed');
    }

    if ($file_input->getSize() > $maxSize * 1024) {
        throw new \Exception('File size too large. Maximum allowed: ' . $maxSize . 'KB');
    }

    // Build path
    $file_name = $i . '-' . time() . '.' . $extension;
    $file_path1 = $upload_folder . '/' . date("Y") . '/' . date("m");

    // Ensure directory exists
    if (!Storage::disk('public')->exists($file_path1)) {
        Storage::disk('public')->makeDirectory($file_path1, 0755, true, true);
    }

    // Store the file
    $storedPath = $file_input->storeAs($file_path1, $file_name, 'public');

    // Return full public path
    return "public/storage/" . $storedPath;
}

function generateThumbnail($pdfPath, $pageNumber = 1, $imgtype = "png", $folder_path = "thumbnails", $i = 0)
{
    $pdfFile = base_path($pdfPath);


    // Check if a file was uploaded
    if (!$pdfFile) {
        return response()->json(['error' => 'No PDF file uploaded.']);
    }
    $file_extension = pathinfo($pdfPath, PATHINFO_EXTENSION);

    // Check if it's a PDF
    if ($file_extension === 'pdf') {
    } else {
        return false;
    }


    // Validate the uploaded file as a PDF
    /*
    $pdfPath->validate([
        'pdf_file' => 'required|mimes:pdf'
    ]);
    */
    // Create a unique filename for the thumbnail
    $thumbnailFileName = 'thumbnail_' . $i . time() . '.png';

    // Path to store the generated thumbnail (inside storage/app/thumbnails)
    //$thumbnailPath = $pdfFile->storeAs('thumbnails', $thumbnailFileName);
    $thumbnailPath = $folder_path . "/" . $thumbnailFileName;
    $thumbnailUrl = "";

    // Create an instance of Pdf from the uploaded PDF file
    //$pdf = new Pdf($pdfFile->path());
    $pdf = new Pdf($pdfFile);

    if (class_exists(\Imagick::class)) {
        // Set options for thumbnail generation (you can adjust these as needed)
        $pdf->setPage($pageNumber)            // Set the page number (e.g., 1 for the first page)
            ->setResolution(73)    // Set the resolution (higher for better quality)
            ->setOutputFormat($imgtype); // Set the output format (png, jpeg, etc.)

        // Save the image to the given path
        $pdf->saveImage(public_path('storage/files/' . $thumbnailPath));

        // Return the URL of the generated thumbnail
        $thumbnailUrl = 'storage/files/' . $thumbnailPath;
    }

    return response()->json(['thumbnail_url' => $thumbnailUrl]);
}

function isValidPalestinianID($id)
{
    // Remove any whitespace
    $id = trim($id);

    // Check length and digits only
    if (!preg_match('/^\d{9}$/', $id)) {
        return false;
    }

    // Optional: Check valid prefixes (based on known IDs: 4, 5, 8, 9)
    $prefix = substr($id, 0, 1);
    if (!in_array($prefix, ['4', '5', '8', '9'])) {
        return false;
    }

    return true;
}

function generatePDF($data, $view, $file_name = "muftah.pdf", $path = "", $type = "D", $logo_loc = "R")
{
    $pdf = new MyPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    // Set RTL mode
    if (\Session("locale") != "en") $pdf->setRTL(true);

    // Add a page
    $pdf->AddPage();
    $pdf->setPageMark();
    $imagePath = 'images/logo.png';
    $imageWidth = 25; // Set your preferred width in millimeters
    $imageHeight = 25; // Set your preferred height in millimeters

    $pageWidth = $pdf->getPageWidth();
    $imageX = $pageWidth - $imageWidth - $pdf->getMargins()['right']; // Align to the right side of the page
    $imageY = $pdf->getY(); // Get the current Y-coordinate of the document

    //To Set the Layout header image
    if ($logo_loc == "C") {
        $pdf->Image($imagePath, 30, 16, $imageWidth, $imageHeight, '', '', '', false, 200, '', false, false, 0, false, false, false);
    } else {
        $pdf->Image($imagePath, $imageX, 16, $imageWidth, $imageHeight, '', '', '', false, 300, '', false, false, 0, false, false, false);
    }

    // Set document information
    $pdf->SetCreator('InterTech');
    $pdf->SetAuthor('InterTech');
    $pdf->SetTitle('PDF');
    $pdf->SetSubject('PDF');
    $pdf->SetKeywords('PDF, example, Laravel, TCPDF');

    // Set header and footer fonts
    $pdf->SetHeaderMargin(5);
    $pdf->setHeaderFont(['dejavusans', '', 10]);
    $pdf->setFooterFont(['dejavusans', '', 8]);
    $pdf->SetFont('dejavusans', '', 8, '', true);


    $pdf->SetMargins(5, 5, 5);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(true, 30);

    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


    // Load the HTML layout file
    $html = view($view, $data)->render();

    // Write the HTML content to the PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Output the generated PDF to the browser
    if ($type == "F" && $path != "") {
        //echo "INSDIE";
        if (strpos($path, "files/") !== false) { //Save PDF to files/
            $pdf->Output(public_path($path . $file_name), 'F');
            return public_path($path . $file_name);
        } else {  //Save to Storage Directory
            $pdf->Output(storage_path($path . $file_name), 'F');
            return storage_path($path . $file_name);
        }
    } else {
        $pdf->Output($file_name, $type);
        return true;
    }
}


function staticPath($path)
{
    $path = str_replace(\Config::get("constants.web_path"), "", $path);
    $path = str_replace("/", "", $path);
    $path = str_replace("", "", $path);
    $path = \Config::get("constants.web_path") . $path;
    return $path;
}

function getImageClass($image, $ratio = 1.91)
{
    $image = str_replace("%20", " ", $image);
    try {

        $imageManager = new ImageManager(new Driver());
        $image = addPublic($image);
        $img = $imageManager->read($image);
        $img_ratio = $img->width() / $img->height();
        if ($img_ratio >= $ratio) return "height-fluid";
        else return "width-fluid";
    } catch (\Exception $e) {
        return "width-fluid";
    }
}

function getImageRatio($path)
{
    try {
        $imageManager = new ImageManager(new Driver());
        $path = addPublic($path);
        $img = $imageManager->read($path);
        $img_ratio = $img->width() / $img->height();
        return $img_ratio;
    } catch (\Exception $e) {
        return 1;
    }
}

function actualResize2($path, $render = false, $crop = true)
{
    if ($path != "") {
        $path = str_replace("%20", " ", $path);
        $is_crop = request()->get("not_crop");

        $crop = true;
        if ($is_crop) $crop = false;

        $full_path = "files/resized/" . $path;

        $temp = explode("/", $path);
        $size = $temp[0];
        $old_path = str_replace("resized/" . $size . "/", "", $full_path);
        /*$tem2=$temp[2];
        $tem3=$temp[3];
        $tem4=$temp[4];*/

        $width = explode("x", $size)[0];
        $height = explode("x", $size)[1];
        $resize_ratio = $width / $height;
        $str_path = str_replace("\\", "/", storage_path());
        $str_path = str_replace("/storage", "", $str_path);

        $old_path = $str_path . processPath($old_path);

        $resize_type = "width";

        $file_name = getFileNameFromPath($old_path);
        $file_path = getResourcePath($old_path);

        $new_path = str_replace("files/image/", "files/resized/" . $size . "/image/", $file_path);
        $new_path = str_replace("files/server/", "files/resized/" . $size . "/server/", $new_path);

        $new_path = public_path() . "/files/resized/";
        //Storage::makeDirectory($new_path);
        $temm = "";
        $tempCount = count($temp) - 1;
        for ($i = 0; $i < $tempCount; $i++) {

            $temm .= $temp[$i] . "\\";
            //$twofolds=str_replace($temm."/", "", $new_path);
            $twofolds = $new_path . $temm;
            //echo $twofolds."<br>";
            if (!FILE::exists($twofolds)) FILE::makeDirectory($twofolds);
        }

        /*if($temp[3]!=null){
            $twofolds=str_replace($tem3."/", "", $new_path);
           //if(!FILE::exists($twofolds)) FILE::makeDirectory($twofolds);
        }

        if($temp[2]!=null){
            $twofolds=str_replace($tem2."/", "", $new_path);
           // if(!FILE::exists($twofolds)) FILE::makeDirectory($twofolds);
        }    */

        if (strpos($new_path, "resized/") === false) $new_path = str_replace("files/", "files/resized/" . $size . "/", $new_path);
        //echo $new_path.$file_name."<bR><bR>";
        //Temporary comment (Remove when you found comment)
        //echo $new_path.$file_name;
        //echo $twofolds.$file_name;
        if (FILE::exists($twofolds . $file_name)) {
            if ($render) return response()->file($new_path . $file_name);
            else return true;
        } //Image file is exists  

        try {
            //echo $new_path."<br>";      
            if (!FILE::exists($twofolds)) FILE::makeDirectory($twofolds);
        } catch (\Exception $e) {
            //echo $new_path."<Br>";
            //echo $e->getMessage()."<Br>";
            //return URL(\config::get("constants.DEFAULT_IMG"));
            return false;
        }

        //If Image not exists do ratio calculation
        try {
            $imageManager = new ImageManager(new Driver());
            $old_path = addPublic($old_path);
            $img = $imageManager->read($old_path);
            $img_ratio = $img->width() / $img->height();
            if ($img_ratio <= $resize_ratio) $resize_type = "width";
            else $resize_type = "height";
        } catch (\Exception $e) {
            //echo "<Br>";  
            //echo $e->getMessage();        
            return false;
        }
        /*if($resize_type == "width"){//Resize To Width

            //echo $new_path.$file_name;
            $img_proc = Image::make($old_path)->resize($width, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            if($crop) $img_proc = $img_proc->crop($width,$height);
            $img = $img_proc->save($twofolds.$file_name);              

            if($render) return response()->file($twofolds.$file_name);                             
            else return true;
        }
        
        else{//Resize to Height         

            $img  = Image::make($old_path)->resize(null, $height, function ($constraint) {
                $constraint->aspectRatio();
            })->crop($width,$height)->save($twofolds.$file_name)->response();
            
        }*/
        if ($resize_type == "width") { //Resize To Width
            $imageManager = new ImageManager(new Driver());
            $old_path = addPublic($old_path);
            $img_proc = $imageManager->read($old_path)->resize($width, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            if ($crop) $img_proc = $img_proc->scale($width, $height);
            $img->cover($width, $height)->save($twofolds . $file_name);

            if ($render) return response()->file($twofolds . $file_name);
            else return true;
        } else { //Resize to Height         
            $imageManager = new ImageManager(new Driver());
            $old_path = addPublic($old_path);
            $img = $imageManager->read($old_path)->resize(null, $height, function ($constraint) {
                $constraint->aspectRatio();
            })->scale($width, $height)->cover($width, $height)->save($twofolds . $file_name);
        }
    } else return false; //In case if there is no conversion
}

function actualResize($path, $render = false, $crop = true, $folder = "public")
{
    if ($path != "") {
        $path = str_replace("%20", " ", $path);
        $is_crop = request()->get("not_crop");

        $crop = true;
        if ($is_crop) $crop = false;

        if ($folder == "storage") $full_path = "storage/resized/" . $path;
        else $full_path = "files/resized/" . $path;

        $temp = explode("/", $path);
        $size = $temp[0];
        $old_path = str_replace("resized/" . $size . "/", "", $full_path);

        $width = explode("x", $size)[0];
        $height = explode("x", $size)[1];
        $resize_ratio = $width / $height;

        if ($folder == "storage") {
            $str_path = str_replace("\\", "/", storage_path() . "/app/public/");
        } else {
            $str_path = str_replace("\\", "/", storage_path());
            $str_path = str_replace("/storage", "", $str_path);
        }


        $old_path = $str_path . processPath($old_path);
        $old_path = str_replace("/app/public/storage", "/app/public/", $old_path);

        $resize_type = "width";

        $file_name = rawurldecode(getFileNameFromPath($old_path));
        $file_path = getResourcePath($old_path);

        $new_path = str_replace(
            ["files/image/", "storage/image/"],
            ["files/resized/" . $size . "/image/", "storage/resized/" . $size . "/image/"],
            $file_path
        );


        $new_path = str_replace(
            ["files/server/", "storage/files/server/"],
            ["files/resized/" . $size . "/server/", $new_path, "storage/resized/" . $size . "/files/server/", $new_path],
            $new_path
        );


        if ($folder == "storage") $new_path = storage_path() . "/app/public/files/resized/";
        else $new_path = public_path() . "/files/resized/";

        //Storage::makeDirectory($new_path);
        $temm = "";
        $tempCount = count($temp) - 1;
        for ($i = 0; $i < $tempCount; $i++) {
            //$temm.=$temp[$i]."\\";
            $temm .= $temp[$i] . "/";
            //$twofolds=$new_path.$temm;
            $twofolds = rawurldecode($new_path . $temm);
            if (!FILE::exists($twofolds)) FILE::makeDirectory($twofolds);
        }

        if (strpos($new_path, "resized/") === false) $new_path = str_replace("files/", "files/resized/" . $size . "/", $new_path);
        if (FILE::exists($twofolds . $file_name)) {
            if ($render) return response()->file($new_path . $file_name);
            else return true;
        } //Image file is exists  

        try {
            if (!FILE::exists($twofolds)) {
                FILE::makeDirectory($twofolds);
            }
        } catch (\Exception $e) {
            //echo "<Br>";  
            //echo $e->getMessage();   
            //return URL(\config::get("constants.DEFAULT_IMG"));
            return false;
        }
        //If Image not exists do ratio calculation
        try {
            $old_path = rawurldecode($old_path);
            $imageManager = new ImageManager(new Driver());
            $old_path = addPublic($old_path, $folder);
            $img = $imageManager->read($old_path);
            $img_ratio = $img->width() / $img->height();
            if ($img_ratio <= $resize_ratio) $resize_type = "width";
            else $resize_type = "height";
        } catch (\Exception $e) {
            return false;
        }
        if ($resize_type == "width") { //Resize To Width
            $imageManager = new ImageManager(new Driver());
            $old_path = addPublic($old_path, $folder);
            $img_proc = $imageManager->read($old_path)->resize($width, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            if ($crop) $img_proc = $img_proc->scale($width, $height);
            $img->cover($width, $height)->save($twofolds . $file_name);

            if ($render) return response()->file($twofolds . $file_name);
            else return true;
        } else { //Resize to Height
            $imageManager = new ImageManager(new Driver());
            $old_path = addPublic($old_path, $folder);
            $img = $imageManager->read($old_path)->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            })
                ->scale($width, $height)->save($twofolds . $file_name);
            /*$img  = Image::make($old_path)->resize(null, $height, function ($constraint) {
                $constraint->aspectRatio();
            })->crop($width,$height)->save($twofolds.$file_name)->response();*/
        }
    } else return false; //In case if there is no conversion
}


function addPublic($path, $folder = "public")
{
    if ($folder == "public" && strpos($path, '/public/') === false) {
        // Insert /public/ before /files/
        $path = str_replace('/files/', '/public/files/', $path);
    }
    return $path;
}

function resizeImage($old_path, $size_group)
{
    $path = $old_path;

    $fileInfo = pathinfo($path);

    if (!isset($fileInfo['extension'])) $extension = "notfound";
    else $extension = strtolower($fileInfo['extension']);

    $unresize_types = ['svg', 'gif', 'jfif', 'bmp', 'notfound'];

    if (in_array($extension, $unresize_types) || (!File::exists(public_path($path)) && !File::exists(storage_path($path)))) return check_http_thumb_replace($path); //if type of image not resizing
    else {
        if (File::exists(public_path($path))) {
            [$actualWidth, $actualHeight] = getimagesize(public_path($path));
        } else {
            [$actualWidth, $actualHeight] = getimagesize(storage_path($path));
        }
    }

    list($targetWidth, $targetHeight) = explode('x', $size_group);
    $targetWidth = (int)$targetWidth;
    $targetHeight = (int)$targetHeight;
    if ($actualWidth <= $targetWidth || $actualHeight <= $targetHeight) return check_http_thumb_replace($path);
    $path = str_replace(
        ["files/", "storage/", "%20"],
        ["files/resized/{$size_group}/", "storage/files/resized/{$size_group}/", " "],
        $path
    );
    if (strpos($path, "http") === false) $path = URL($path);

    /////// Get image relative path to call resize function ///////
    if (strpos($path, '/storage/') !== false) { //if $path contains /storage/
        $tmp = explode("resized/", $path);
        if (count($tmp) > 1) {
            $tmp_path = $tmp[1];
            $result = actualResize($tmp_path, false, true, "storage"); //Call resizing without rendering the resized Image
        }
    } else {
        $tmp = explode("resized/", $path);
        if (count($tmp) > 1) {
            $tmp_path = $tmp[1];
            $result = actualResize($tmp_path, false, true, "public"); //Call resizing without rendering the resized Image
        }
    }

    //$path=str_replace("/public/public/","/public/",$path);
    //$path=str_replace("/public//public/","/public/",$path);

    return $path;
}

function getFileNameFromPath($fullpath)
{
    return basename($fullpath);
}

function getResourcePath($fullpath)
{
    $temp = explode("/", $fullpath);
    unset($temp[count($temp) - 1]);
    //unset($temp[0]);

    $new_path = implode("/", $temp);

    return $new_path . "/";
}

function addUpcomingCond($query)
{
    $res_query = $query->where(function ($query) {
        $date_now = date("Y-m-d");
        $query->where("ev_start_date", ">", $date_now)
            ->orwhere(function ($query) use ($date_now) {
                $query->where("ev_end_date", ">", $date_now)
                    ->orwhere(function ($query) use ($date_now) {
                        $time_now = date("H:i");
                        $query->where("ev_end_date", "=", $date_now)
                            ->where("ev_end_time", ">=", $time_now);
                    });
            });
    });
    return $res_query;
}

function generateUserToken($u_id)
{
    return bcrypt($u_id . time());
}


/*function selectResource($column, $as, $size = "750x400")
{
    //return "IF(".$column."='','',CONCAT('".URL('')."',REPLACE(".$column.",'".\config::get("constants.FOLDER_NAME")."','/'))) as ".$as;
    //return "IFNULL(IF(".$column."='','',IF(".$column." like '%http%',".$column.",CONCAT('".URL('')."',REPLACE(".$column.",'".\config::get("constants.FOLDER_NAME")."','/')))),'') as ".$as."";
    return "IFNULL(IF(" . $column . "='','',IF(" . $column . " like '%http%'," . $column . ",REPLACE(CONCAT('" . URL('') . "',REPLACE(" . $column . ",'" . \Config::get("constants.FOLDER_NAME") . "','/')),'files/','/files/resized/" . $size . "/'))),'') as " . $as . "";
}*/

function selectResource($column, $as, $size = "")
{
    $folder = rtrim(\Config::get("constants.FOLDER_NAME"), '/') . '/'; // ensure trailing slash
    $baseUrl = rtrim(URL('/'), '/') . '/'; // ensure base URL ends with /

    if ($size == "") {
        return "IFNULL(IF($column = '', '', IF($column LIKE '%http%', $column, CONCAT('$baseUrl', REPLACE($column, '$folder', '')))), '') AS $as";
    } else {
        return "IFNULL(IF($column = '', '', IF($column LIKE '%http%', $column, REPLACE(CONCAT('$baseUrl', REPLACE($column, '$folder', '')), 'files/', 'files/resized/$size/'))), '') AS $as";
    }
}

function encode_thumbs($product)
{
    //echo basename($product->p_thumb);

    //$product->p_thumb=str_replace(" ", "%20", $product->p_thumb);
    //$conv = iconv('utf-8', 'windows-1256', basename($product->p_thumb)); 

    //echo "xxxxx".is_arabic(basename($product->p_thumb))."xxxxxxx";

    //$product->p_thumb=str_replace(" ", "%20", $product->p_thumb);
    for ($i = 1; $i <= 6; $i++) {
        $property = ($i === 1) ? 'p_thumb' : 'p_thumb' . $i;
        $value = $product->$property;
        $thumb = substr($value, strrpos($value, '/') + 1);
        if (is_arabic($thumb) > 0) {
            $encode_thumbname = urlencode($thumb);
            $value = str_replace($thumb, $encode_thumbname, $value);
            $value = str_replace("+", "%20", $value);
            $product->$property = $value;
        }
    }

    return $product;
}

function aman_format($number)
{
    if ($number == "") $number = 0;
    if ($number >= 1000000) {
        return "<span class='bug-num' >" . number_format(($number / 1000000000), 2, ".", ",") . "</span> <br><span class='curr' >" . trans("messages.miliar") . " " . trans("messages.nis") . "</span>";
    } else return number_format($number, 0, "", ",");
}

function numberInMiliar($number)
{
    if ($number == "") $number = 0;
    return number_format(($number / 1000000000), 3, ".", ",");
}

function numberInMillion($number)
{
    if ($number == "") $number = 0;
    return number_format(($number / 1000000), 2, ".", "");
}

function processPath($path)
{

    if (strpos($path, "http") === false) {

        $folder_name = Config('constants.web_folder');
        if ($folder_name != "") $path = str_replace("/" . $folder_name, "", $path);
        //if($folder_name!="") $path = str_replace($folder_name,"",$path);
        if ($folder_name != "") $path = str_replace("/" . $folder_name . "/", "/", $path);
        $path = str_replace("/files/", "files/", $path);  //To remove all (files/) from the Path
        $path = str_replace("files/", "files/", $path); //To remove all (files/) from the Path
        $path = str_replace("files/", "/files/", $path);

        /*if(strpos($_SERVER['SERVER_NAME'],"clients.intertech.ps") !== false){
            if($folder_name!="") $path = "/".$folder_name.$path;
        }*/

        //$path = str_replace("files/","files/",$path);
        //$path = "".$path;
    }

    $path = str_replace("\\", "/", $path);
    return $path;
}

function processLink($path)
{
    $folder_name = Config('constants.web_folder');
    //$path = str_replace($folder_name,"",$path);
    $path = str_replace("/" . $folder_name . "/", "/", $path);
    return $path;
}

function filterBody($body)
{
    $folder_name = Config('constants.web_folder');
    $web_path = URL::asset('');

    if ($folder_name != "") $body = str_replace("/$folder_name/", "", $body);
    $body = str_replace("files\\", "files/", $body);
    $body = str_replace("/", "/", $body);
    $body = str_replace("", "/", $body);
    $body = str_replace("files/", "files/", $body);
    $body = str_replace("/images/", "images/", $body);
    $body = str_replace("images/", $web_path . "images/", $body);

    $body = str_replace("/files", "files", $body);
    $body = str_replace("files", $web_path . "files", $body);
    //$body=str_replace("files/server",$web_path."files/server/",$body);
    //$body=str_replace("files/image/",$web_path."files/image/",$body);

    $body = str_replace("&lsquo;", "'",  $body);
    $body = str_replace("&#34;", "\"",  $body);
    return $body;
}

function myUrlEncode($string)
{
    $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
    $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
    return str_replace($replacements, $entities, $string);
}

function friendly_encode($title)
{
    $removeChars = ['.', '"', '%', ':', '|', '!', '~', '?', '/', "\r"];
    $title = str_replace($removeChars, "", $title);
    $title = str_replace(" ", "-", $title);
    $title = preg_replace("/[\/\&%#\$]/", "", $title);
    return $title;
}

function friendly_decode($title)
{
    $title = str_replace("-", " ", $title);
    $title = urldecode($title);
    return $title;
}

function generateSlug($string, $delimiter = '-')
{
    $string = mb_strtolower($string, 'UTF-8');
    // Remove any non-alphanumeric characters, except for the delimiter
    $string = preg_replace('/[^a-z0-9\-_]+/u', $delimiter, $string);
    // Remove duplicate delimiters
    $string = preg_replace('/' . preg_quote($delimiter, '/') . '+/', $delimiter, $string);
    $string = trim($string, $delimiter);

    return $string;
}

function check_session_end()
{
    // To Make Session Die After a period of time 
    define('SESSION_TIMEOUT', 900); //15 Mins
    if (isset($_SESSION['time'])) $idletime = time() - $_SESSION['time'];
    else $idletime = time();
    if ($idletime > SESSION_TIMEOUT) {
        //Reset session
        @session_destroy();
        @session_start();
    }
    $_SESSION['time'] = time();
    // End
}

function month_name($month)
{
    switch ($month) {
        case "1":
            $month = "January";
            break;
        case "2":
            $month = "February";
            break;
        case "3":
            $month = "March";
            break;
        case "4":
            $month = "April";
            break;
        case "5":
            $month = "May";
            break;
        case "6":
            $month = "June";
            break;
        case "7":
            $month = "July";
            break;
        case "8":
            $month = "August";
            break;
        case "9":
            $month = "September";
            break;
        case "10":
            $month = "October";
            break;
        case "11":
            $month = "November";
            break;
        case "12":
            $month = "December";
            break;
    } //switch
    return $month;
}

function day_name($day)
{
    switch ($day) {
        case "Sunday":
            $day = "الأحد";
            break;
        case "Monday":
            $day = "الإثنين";
            break;
        case "Tuesday":
            $day = "الثلاثاء";
            break;
        case "Wednesday":
            $day = "الأربعاء";
            break;
        case "Thursday":
            $day = "الخميس";
            break;
        case "Friday":
            $day = "الجمعة";
            break;
        case "Saturday":
            $day = "السبت";
            break;
    }
    return ($day);
}

function fdate($date)
{
    $d = array();
    $d = explode('-', $date);
    $day = $d[2];
    $month = $d[1];
    $year = $d[0];
    switch ($month) {
        case "1":
            $month = "January";
            break;
        case "2":
            $month = "February";
            break;
        case "3":
            $month = "March";
            break;
        case "4":
            $month = "April";
            break;
        case "5":
            $month = "May";
            break;
        case "6":
            $month = "June";
            break;
        case "7":
            $month = "July";
            break;

        case "8":
            $month = "August";
            break;
        case "9":
            $month = "September";
            break;
        case "10":
            $month = "October";
            break;
        case "11":
            $month = "November";
            break;
        case "12":
            $month = "December";
            break;
    }
    $date = $day . " " . $month . " " . $year;
    return ($date);
}

function fdate1($date)
{
    $d = array();
    $d = explode('-', $date);
    $day = $d[2];
    $month = $d[1];
    $year = $d[0];
    if (\Session('prefix') == "ar_") {
        switch ($month) {
            case "1":
                $month = "كانون الثاني";
                break;
            case "2":
                $month = "شباط";
                break;
            case "3":
                $month = "آذار";
                break;
            case "4":
                $month = "نيسان";
                break;
            case "5":
                $month = "أيار";
                break;
            case "6":
                $month = "حزيران";
                break;
            case "7":
                $month = "تموز";
                break;

            case "8":
                $month = "آب";
                break;
            case "9":
                $month = "أيلول";
                break;
            case "10":
                $month = "تشرين الأول";
                break;
            case "11":
                $month = "تشرين الثاني";
                break;
            case "12":
                $month = "كانون الأول";
                break;
        }
    } else {
        switch ($month) {
            case "1":
                $month = "Jan";
                break;
            case "2":
                $month = "Feb";
                break;
            case "3":
                $month = "Mar";
                break;
            case "4":
                $month = "Apr";
                break;
            case "5":
                $month = "May";
                break;
            case "6":
                $month = "June";
                break;
            case "7":
                $month = "July";
                break;

            case "8":
                $month = "Aug";
                break;
            case "9":
                $month = "Sep";
                break;
            case "10":
                $month = "Oct";
                break;
            case "11":
                $month = "Nov";
                break;
            case "12":
                $month = "Dec";
                break;
        } //switch
    }
    $date = $day . " " . $month . " " . $year;
    return ($date);
}

function adate($date)
{
    $d = array();
    $d = explode('-', $date);
    $day = $d[2];
    $month = $d[1];
    $year = $d[0];
    switch ($month) {
        case "1":
            $month = "كانون الثاني";
            break;
        case "2":
            $month = "شباط";
            break;
        case "3":
            $month = "آذار";
            break;
        case "4":
            $month = "نيسان";
            break;
        case "5":
            $month = "أيار";
            break;
        case "6":
            $month = "حزيران";
            break;
        case "7":
            $month = "تموز";
            break;

        case "8":
            $month = "آب";
            break;
        case "9":
            $month = "أيلول";
            break;
        case "10":
            $month = "تشرين الأول";
            break;
        case "11":
            $month = "تشرين الثاني";
            break;
        case "12":
            $month = "كانون الأول";
            break;
    }
    $date = $day . " " . $month . " " . $year;
    return ($date);
}

function along_date($date)
{
    $temp = explode("-", $date);
    $day = $temp[2];
    $month = $temp[1];
    $year = $temp[0];

    return $day . " " . amonth_name($month) . " " . $year;
}

function long_date($date)
{
    $temp = explode("-", $date);
    $day = $temp[2];
    $month = $temp[1];
    $year = $temp[0];

    return $day . "th " . month_name($month) . " " . $year;
}

function amonth_name($month)
{
    switch ($month) {
        case "1":
            $month = "كانون الثاني";
            break;
        case "2":
            $month = "شباط";
            break;
        case "3":
            $month = "آذار";
            break;
        case "4":
            $month = "نيسان";
            break;
        case "5":
            $month = "أيار";
            break;
        case "6":
            $month = "حزيران";
            break;
        case "7":
            $month = "تموز";
            break;

        case "8":
            $month = "آب";
            break;
        case "9":
            $month = "أيلول";
            break;
        case "10":
            $month = "تشرين الأول";
            break;
        case "11":
            $month = "تشرين الثاني";
            break;
        case "12":
            $month = "كانون الأول";
            break;
    }
    return ($month);
}

function getSubString($text, $length)
{
    $old_title = $text;
    $text = substr($text, 0, $length);
    for ($i = $length; $i < strlen($old_title); $i++) {
        if (substr($old_title, $i, 1) == " ") break;
        $text .= substr($old_title, $i, 1);
    }
    if (strlen($text) < strlen($old_title)) $text .= "...";
    return $text;
}

function filterTitle($title)
{
    $title = str_replace("&lsquo;", "'",  $title);
    $title = str_replace("&#34;", "\"",  $title);
    $title = preg_replace('[<.*?.>]', '', $title);
    $title = preg_replace('[<]', '', $title);
    $title = preg_replace('[>]', '', $title);
    return $title;
}

function clear($val)
{
    $val = strtolower($val);
    // Remove common SQL and injection-related keywords/special characters
    $toRemove = [
        'sql','union','injection','select','update','insert','delete','from','+','(',')',',','&',"'",'xor','or','and'
    ];
    $val = str_replace($toRemove, '', $val);
    // Remove any strings enclosed in angle brackets (basic tag stripping)
    $val = preg_replace('/<.*?>/', '', $val);
    return ($val);
}

function post($val)
{
    $val = str_replace("&lsquo;", "'", $val);
    $val = str_replace("&#34;", "\"", $val);
    return ($val);
}

function get_extension($filename)
{
    $temp = explode(".", $filename);
    $ext = $temp[count($temp) - 1];
    return $ext;
}

function get_thumb($pic, $width)
{
    global $uploadfolder;
    $pic = str_replace("/" . $uploadfolder . "/", "", $pic);
    $ext = array();
    $ext = explode('.', $pic);
    $z = sizeof($ext) - 1;
    if ($ext[$z] == "jpg" || $ext[$z] == "JPG") {
        $t = "includes/thumbjpg.php?w=" . $width . "&im=";
        $p = "../" . $pic;
        $file = $t . $p;
        //$pic = "<img src='". $file ."' border=0 align='left' hspace=7><br>";
    } elseif ($ext[$z] == "gif" || $ext[$z] == "GIF") {
        $t = "includes/thumbgif.php?w=" . $width . "&im=";
        $p = "../" . $pic;
        $file = $t . $p;
        //$pic = "<img src='". $file ."' border=0 align='left' hspace=7><br>";
    } else {
        $file = $pic;
        //$pic = "<img src='../userfiles/". $pic ."' width='".$width."' border=0 align='left' hspace=7>";
    }
    return $file;
}

function thumb($pic, $w)
{
    $ext = array();
    $ext = explode('.', $pic);
    $e = sizeof($ext) - 1;
    $p = "includes/thumb.php?ext=" . $e . "&w=" . $w . "&im=../" . $pic;
    return ($p);
}

function genid_old($id)
{
    $id = genRandomString_old() . genRandomString_old() . "a" . ($id * 951) . "A" . genRandomString_old() . genRandomString_old();
    return ($id);
}

function regenid_old($id)
{
    $temp = substr($id, 11);
    $pos = strpos($temp, "A");
    $sid = substr($temp, 0, $pos);
    //echo $temp;
    $id = (int)$sid / 951;
    return ($id);
}

function genRandomString_old()
{
    $length = 5;
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzAQWERTYUIOPSDFGHJKLZXCVBNM";
    $string = "";
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, 61)];
    }
    return $string;
}

function genid_new($id)
{
    $newid = $id * 951;
    $id = genRandomString_new($newid) . "y" . ($newid) . "Y" . genRandomString_new($newid);
    return ($id);
}

function regenid_new($id)
{
    if (is_numeric($id)) {
        return $id;
    } else {
        $res = explode("y", $id);
        if (count($res) > 1) {
            $res1 = explode("Y", $res[1]);
            $old_id = $res1[0];
            $id = (int)$old_id / 951;
            return ($id);
        } else return 0;
    }
}

function regenid_new2($id)
{
    $res = explode("y", $id);
    if (count($res) > 1) {
        $res1 = explode("Y", $res[1]);
        $old_id = $res1[0];
        $id = (int)$old_id / 951;
        return ($id);
    } else return 0;
}

function genRandomString_new($id)
{
    $string = dechex($id);
    return $string;
}

function genid($id)
{
    //return genid_new($id);
    return $id;
}

function regenid($id)
{
    $ret_id = 0;
    $ret_id = regenid_new($id);
    if ($ret_id == 0) {
        $ret_id = regenid_old($id);
    }
    //return $ret_id;
    return $id;
    /*if(strlen($id) > 22)
	{
		return regenid_old($id);
	} 
	else
	{
		return regenid_new($id);
	}*/
}

function genid2($id)
{
    return genid_new($id);
}

function regenid2($id)
{
    $ret_id = 0;
    $ret_id = regenid_new2($id);
    if ($ret_id == 0) {
        $ret_id = regenid_old($id);
    }
    return $ret_id;
}

function add_log($user_id, $user_name, $action_type, $affected, $affected_id, $action, $notes, $title)
{
    global $current_time;
    global $sqlconnect;
    $ip = $_SERVER['REMOTE_ADDR'];
    $time = $current_time;
    $notes = request($notes);
    $action = request($action);
    $action_type = request($action_type);
    $title = request($title);

    $result = mysqli_query($sqlconnect, "insert into logs (ip,datetime,user_name,user_id,action_type,affected,affected_id,action,notes,item_title) values('$ip','$time','$user_name','$user_id','$action_type','$affected','$affected_id','$action','$notes','$title')");
    echo mysqli_error($sqlconnect);
}

function getBeforeText($date)
{
    $bef_txt = "";
    $diff_time = time() - strtotime($date);

    if ($diff_time >= 86400) {
        $bef_time = intVal($diff_time / (60 * 60 * 24));
        $bef_txt = "قبل " . $bef_time . " يوم";
    } else if ($diff_time >= 3600) {
        $bef_time = intVal($diff_time / (60 * 60));
        $bef_txt = "قبل " . $bef_time . " ساعات";
    } else if ($diff_time >= 60) {
        $bef_time = intVal($diff_time / (60));
        $bef_txt = "قبل " . $bef_time . " دقيقة";
    } else {
        $bef_txt = "الآن";
    }
    return $bef_txt;
}

function resize_embed($embed, $width, $height)
{
    if (strpos($embed, 'youtube') !== false) {
        $pos1 = strpos($embed, "width");
        $old_width = substr($embed, $pos1, 11);
        $embed = str_replace($old_width, "width33='" . $width . "'", $embed);
        // Second Width
        $pos2 = strpos($embed, "width");
        $old_width = substr($embed, $pos2, 11);
        $embed = str_replace($old_width, "width='" . $width . "'", $embed);
        $embed = str_replace("width33", "width", $embed);


        $pos1 = strpos($embed, "height");
        if ($pos1 !== false) {
            $old_height = substr($embed, $pos1, 11);
            $embed = str_replace($old_height, "height33='" . $height . "'", $embed);
            // Second Width
            $pos2 = strpos($embed, "height");
            $old_height = substr($embed, $pos2, 11);
            $embed = str_replace($old_height, "height='" . $height . "'", $embed);
            $embed = str_replace("height33", "height", $embed);
        }
    }
    return $embed;
}

function get_youtube_id($link)
{
    $parts = parse_url($link);
    if (isset($parts['host']) && $parts['host'] == "youtu.be") {
        $id = str_replace("/", "", $parts['path']);
        if (strpos($id, '?') !== false) {
            // Remove the '?' and everything after it
            $id = strstr($id, '?', true);
        }
    } else if (isset($parts['query'])) {
        parse_str($parts['query'], $query);
        $id = $query['v'];
    }

    return $id;
}

function getYouTubeThumbnail($video_link)
{
    // Check if the link is a YouTube URL
    if (preg_match('/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $video_link, $matches)) {

        // Extract the video ID
        $video_id = $matches[1];

        // Construct the URL for the default thumbnail image
        $thumbnail_url = "https://img.youtube.com/vi/$video_id/maxresdefault.jpg";

        return $thumbnail_url;
    } else {
        // If not a YouTube link, return null or some default image URL
        return null; // Or provide a default image URL, e.g. '/path/to/default/image.jpg'
    }
}

function is_image($filename)
{
    $img_ext = array('png', 'gif', 'jpg', 'bmp', 'jpeg', 'webp', 'svg');
    $temp = explode(".", $filename);
    $ext = strtolower($temp[count($temp) - 1]);
    $check = in_array($ext, $img_ext);
    return $check;
}

function send_smtp($from_email, $from_name, $to_email, $to_name, $subject, $msg, $attachment)
{
    $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
    try {

        $mail->SMTPDebug = env("MAIL_DEBUG");                               // Enable verbose debug output
        if (env('MAIL_MAILER') == "smtp") $mail->isSMTP();
        $mail->IsHTML(env("MAIL_HTML"));
        $mail->CharSet = "UTF-8";                                   // Set mailer to use SMTP
        $mail->Host = env('MAIL_HOST');  // Must be Domain (Not accept IP Address) Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = env('MAIL_USERNAME');                 // SMTP username
        $mail->Password = env('MAIL_PASSWORD');                           // SMTP password
        $mail->SMTPSecure = env('MAIL_ENCRYPTION');                       // Enable TLS encryption, `ssl` also accepted
        $mail->Port = env('MAIL_PORT');                                   // TCP port to connect to
        $mail->setFrom(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"));

        //Recipients
        if (!is_array($to_email)) {
            $mail->addAddress($to_email, $to_name);     // Add a recipient
        } else {
            $i = 0;
            foreach ($to_email as $user) {
                if ($i == 0) $mail->addAddress($user, $to_name);     // Add a recipient        
                else $mail->addBCC($user);
                $i++;
            }
        }

        //Content     
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;

        $msg_body = $msg;
        $mail->Body = $msg_body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
        return false;
    }
}

function send_ticket($from_email, $from_name, $to_email, $to_name, $subject, $msg, $attachment)
{
    $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
    try {
        $mail->SMTPDebug = env("MAIL_DEBUG");                               // Enable verbose debug output
        if (env('MAIL_MAILER') == "smtp") $mail->isSMTP();
        $mail->IsHTML(env("MAIL_HTML"));
        $mail->CharSet = "UTF-8";                                   // Set mailer to use SMTP
        $mail->Host = env('MAIL_HOST');  // Must be Domain (Not accept IP Address) Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = env('MAIL_USERNAME');                 // SMTP username
        $mail->Password = env('MAIL_PASSWORD');                           // SMTP password
        $mail->SMTPSecure = env('MAIL_ENCRYPTION');                       // Enable TLS encryption, `ssl` also accepted
        $mail->Port = env('MAIL_PORT');                                   // TCP port to connect to
        $mail->setFrom(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"));

        if (!is_array($to_email)) {
            $mail->addAddress($to_email, $to_name);     // Add a recipient
        } else {
            $i = 0;
            foreach ($to_email as $user) {
                if ($i == 0) $mail->addAddress($user['u_email'], $to_name);     // Add a recipient        
                else $mail->addBCC($user['u_email']);
                $i++;
            }
        }
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;

        $mail->Body = $msg;
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        //$mail->SMTPDebug = 2;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
        //echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

function bulk_smtp($to_list, $to_name, $subject, $msg, $attachment)
{
    $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
    try {
        $mail->SMTPDebug = env("MAIL_DEBUG");                               // Enable verbose debug output
        if (env('MAIL_MAILER') == "smtp") $mail->isSMTP();
        $mail->IsHTML(env("MAIL_HTML"));
        $mail->CharSet = "UTF-8";                                   // Set mailer to use SMTP
        $mail->Host = env('MAIL_HOST');  // Must be Domain (Not accept IP Address) Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = env('MAIL_USERNAME');                 // SMTP username
        $mail->Password = env('MAIL_PASSWORD');                           // SMTP password
        $mail->SMTPSecure = env('MAIL_ENCRYPTION');                       // Enable TLS encryption, `ssl` also accepted
        $mail->Port = env('MAIL_PORT');                                   // TCP port to connect to
        $mail->setFrom(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"));

        if (is_array($to_list)) {
            $mail->addAddress($to_list, $to_name);     // Add a recipient
        } else {
            $i = 0;
            //print_r($to_list);
            foreach ($to_list as $item) {
                if ($i == 0) $mail->addAddress($item['u_email'], $to_name);     // Add a recipient        
                else $mail->addBCC($item['u_email']);
                $i++;
            }
        }
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;

        $msg_body = '<!DOCTYPE html><html><head><style>*,body, html{    font-family:verdana;    font-size:12px;}</style></head><body>
        <div style="float:none;margin-right:auto;margin-left:auto;border:solid 10px #69559B;min-height:400px;padding:15px;max-width:650px;"  >
        <div style="float:left;width:50%;text-align:left;"><a href="' . URL::asset('') . '" target="_blank"><img src="' . URL::asset('images/logo.png') . '" style="width:130px;" align="left" width=150 ></a></div>
        <div style="float:right;width:50%;text-align:right;padding-top:20px;" align="right">' . fdate(date("Y-m-d")) . '</div>
        <br style="clear:both;">
        <div style="width:100%;border-bottom:solid 1px #ccc;margin-top:20px;" ></div>
        <div style="padding-top:20px;text-align:left;" >' . $msg . '</div></div></body></html>';

        $mail->Body = $msg_body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function upload_file($file, $file_name, $type)
{
    $upload_folder = "../files/image/";
    $success = 1;
    $cdate = date("YdmHis");
    $file_size = filesize($file['tmp_name']);
    $whitelist = array('jpg', 'png', 'gif', 'jpeg', 'webp', 'svg');
    $blacklist = array('php', 'php3', 'php4', 'js', 'html', 'json', 'asp', 'aspx', 'phtml', 'exe');
    global $mime_types;
    global $whitelist_mimes;

    if ($file_size < 2000000) {
        $imagesizedata = getimagesize($file['tmp_name']);
        if ($type == "1") { //Image File to upload
            if ($imagesizedata === FALSE) {
                //not image
                $success = 3;
            } else {
                //image
                //use $imagesizedata to get extra info
                if ($imagesizedata['mime'] != 'image/gif' && $imagesizedata['mime'] != 'image/jpeg' && $imagesizedata['mime'] != 'image/png' && $imagesizedata['mime'] != 'image/jpg' && isset($imagesizedata)) {
                    $success = 3;
                } else {
                    if (move_uploaded_file($file['tmp_name'], $upload_folder . $file_name)) {
                    } else $success = 0;
                }
            }
        } else { //Not Image Upload (PDF/Doc/XLS)
            if (in_array(get_extension($file['name']), $blacklist)) {
                //File uploaded is blacklisted
                $success =  4; //Invalid File
            } else {
                //Check mime		
                $file_mime = $file['type'];
                if (in_array($file_mime, $whitelist_mimes)) {
                    $upload_folder = "../files/server/";
                    if (move_uploaded_file($file['tmp_name'], $upload_folder . $file_name)) {
                        $success = 1;
                    } else $success = 0;
                } else {
                    $success = 4; //invalid file
                }
            }
        }
    } else $success = 2;
    //Success = 1     // Success
    //Success = 0 	  // Failed
    //Success = 2     // Size is Larget
    //Success = 3     // Not image
    //Success = 4     // Invalid File Type
    return $success;
}

function upload_multi_file($file, $file_name, $type)
{
    $upload_folder = "../files/image/";
    $success = 1;
    $cdate = date("YdmHis");
    $file_size = filesize($file['tmp_name'][0]);
    $whitelist = array('jpg', 'png', 'gif', 'jpeg', 'webp', 'svg');
    $blacklist = array('php', 'php3', 'php4', 'js', 'html', 'json', 'asp', 'aspx', 'phtml', 'exe');
    global $mime_types;
    global $whitelist_mimes;

    if ($file_size < 2000000) {
        $imagesizedata = getimagesize($file['tmp_name'][0]);
        if ($type == "1") { //Image File to upload
            if ($imagesizedata === FALSE) {
                //not image
                $success = 3;
            } else {
                //image
                //use $imagesizedata to get extra info
                if ($imagesizedata['mime'] != 'image/gif' && $imagesizedata['mime'] != 'image/jpeg' && $imagesizedata['mime'] != 'image/png' && $imagesizedata['mime'] != 'image/jpg' && isset($imagesizedata)) {
                    $success = 3;
                } else {
                    if (move_uploaded_file($file['tmp_name'][0], $upload_folder . $file_name)) {
                    } else $success = 0;
                }
            }
        } else { //Not Image Upload (PDF/Doc/XLS)
            if (in_array(get_extension($file['name'][0]), $blacklist)) {
                //File uploaded is blacklisted
                $success =  4; //Invalid File
            } else {
                //Check mime		
                $file_mime = $file['type'][0];
                if (in_array($file_mime, $whitelist_mimes)) {
                    $upload_folder = "../files/server/";
                    if (move_uploaded_file($file['tmp_name'][0], $upload_folder . $file_name)) {
                        $success = 1;
                    } else $success = 0;
                } else {
                    $success = 4; //invalid file
                }
            }
        }
    } else $success = 2;
    //Success = 1     // Success
    //Success = 0 	  // Failed
    //Success = 2     // Size is Larget
    //Success = 3     // Not image
    //Success = 4     // Invalid File Type
    return $success;
}

function friendly_img($body)
{
    $folder_name = Config('constants.web_folder');
    $web_path = asset('');
    $web_path1 = asset('');

    $body = str_replace("/" . $folder_name . "/", "/", $body);
    if ((strpos($body, 'http://') !== false) or (strpos($body, 'https://') !== false)) {
    } else {
        $body = str_replace("", "", $body);
        $body = str_replace("/files/", "files/", $body);
        $body = str_replace("files/", $web_path . "files/", $body);
    }
    return $body;
}

function CheckQuotations($body)
{
    $body = str_replace('//!', '<div class="body_box"><i class="fas fa-quote-right"></i>&nbsp;', $body);
    $body = str_replace('!//', '&nbsp;<i class="fas fa-quote-left"></i></div>', $body);
    $body = str_replace('<blockquote>', '<div class="body_box"><i class="fas fa-quote-right"></i>&nbsp;', $body);
    $body = str_replace('</blockquote>', '&nbsp;<i class="fas fa-quote-left"></i></div>', $body);

    return $body;
}

function body_replace($thumb, $folder_name)
{
    $thumb = str_replace("/" . $folder_name . "/", "/", $thumb);
    return $thumb;
}

function filterBody2($title)
{
    $title = str_replace("&lsquo;", "'",  $title);
    $title = str_replace("&#34;", "\"",  $title);
    $title = str_replace('"', '', $title);
    $title = str_replace("'", "", $title);
    return $title;
}

function filterImage($image)
{
    global $web_path;
    global $folder_name;
    $image = str_replace("/" . $folder_name . "/", "/", $image);
    return $web_path . $image;
}

function thumb_replace($thumb, $folder_name = "")
{
    $folder_name = Config('constants.web_folder');
    $thumb = str_replace("\\", "/", $thumb);
    $thumb = str_replace("/" . $folder_name . "/", "/", $thumb);
    return $thumb;
}

function check_http($link)
{
    if ((strpos($link, 'http://') !== false) or (strpos($link, 'https://') !== false)) {
        return 1;
    } else return 0;
}

function check_http_thumb_replace($link)
{
    $web_path = asset('');
    $web_path1 = asset('');
    if ((strpos($link, 'http://') !== false) or (strpos($link, 'https://') !== false)) {
        $link = preg_replace('#([^:])//+#', '$1/', $link);
        return $link;
    } else {
        $link = $web_path . thumb_replace($link);
        return  preg_replace('#([^:])//+#', '$1/', $link);
    }
}

function check_FILE_link($path)
{ //USED ONLY FOR PREVIEW FILES
    $folder_name = Config('constants.web_folder');
    $path = str_replace("\\", "/", $path);
    $path = str_replace("/", "", $path);
    $path = str_replace("/" . $folder_name . "/", "/", $path);
    return $path;
}

function sendSMS($mobile, $msg)
{
    $object = file_get_contents("http://smsservice.hadara.ps:4545/SMS.ashx/bulkservice/sessionvalue/sendmessage/?apikey=" . \Config::get("constants.SMS_API") . "&to=" . $mobile . "&msg=" . urlencode($msg));
    return true;
}

function CheckImageFound($url)
{
    $imageData = @file_get_contents($url);
    if ($imageData !== false) {
        // Image exists, you can now get additional information if needed
        $imageInfo = getimagesizefromstring($imageData);

        // Check if the result from getimagesize() is valid
        if ($imageInfo !== false) {
            //echo 'Image found!';
            return true;
            // You can access image width and height using $imageInfo[0] and $imageInfo[1]
        } else {
            //echo 'Invalid image format or unable to get image information.';
            return false;
        }
    } else {
        return false;
        //echo 'Image not found or unable to access the URL.';
    }
}

function get_youtube_id_embed($source)
{
    preg_match('/src="([^"]+)"/', $source, $match);
    return $source;
}

function get_youtube_id_from_embed($source)
{
    preg_match('/src="([^"]+)"/', $source, $match);
    $url = "";
    foreach (array_slice($match, 1) as $mm) {
        $url = $mm;
    }
    $get_id = explode("/", $url);
    $id = $get_id[count($get_id) - 1];
    return $id;
}

function generate_body_to_glossary($p_body, $glossary_items)
{
    foreach ($glossary_items as $item) {
        $p_body = str_replace($item->glossary_ar_name, " <a href='#' class='glossary_link glossary_" . $item->glossary_id . "' data-id='" . $item->glossary_id . "' >\"" . $item->glossary_ar_name . "\"</a>", $p_body);
    }
    return $p_body;
}

function str_replace_limit($search, $replace, $string, $limit = 1)
{
    $pos = strpos($string, $search);
    if ($pos === false) {
        return $string;
    }

    $searchLen = strlen($search);
    for ($i = 0; $i < $limit; $i++) {
        $string = substr_replace($string, $replace, $pos, $searchLen);

        $pos = strpos($string, $search);

        if ($pos === false) {
            break;
        }
    }

    return $string;
}

function isImage($path)
{
    if ($path == "") return false;

    $temp = explode("/", $path);
    $file = $temp[count($temp) - 1];

    $ext = get_extension($file);
    if (strtolower($ext) == "png" || strtolower($ext) == "jpg" || strtolower($ext) == "gif" || strtolower($ext) == "svg" || strtolower($ext) == "webp") return true;
    else return false;
}

function gen_string($string, $max)
{
    $string=strip_tags($string);
    if (strlen($string) > $max) {
        $tok = strtok($string, ' ');
        $string = '';
        $string2 = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);

        while ($tok !== false && count($string2) < $max) {
            $string2 = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
            $tok2 = preg_split('//u', $tok, -1, PREG_SPLIT_NO_EMPTY);

            if (count($string2) + count($tok2) <= $max)
                $string .= $tok . ' ';
            else
                break;
            $tok = strtok(' ');
        }
        return trim($string) . ' ...';
    } else return trim($string);
}

/*function resize_thumb($size)
{
    global $folder_name;
    list($width, $height, $type, $attr) = getimagesize($web_path . thumb_replace($thumb));
    $styles = "";
    if ($width >= $height) $styles = "max-width:" . $size . "px;width:100%;";
    else if ($height > $width) $styles = "max-height:" . $size . "px;height:100%;";
    return $styles;
}*/

function resize_thumb($size,$thumb_link){
    list($width, $height, $type, $attr) = getimagesize($thumb_link);
    /*echo "Image width " .$width;
    echo "<BR>";
    echo "Image height " .$height;
    echo "<BR>";
    echo "Image type " .$type;
    echo "<BR>";
    echo "Attribute " .$attr;*/
    $styles="";
    if($width =="" || $height ==""){
        $styles="width:auto !important;max-height:".$size."px;";
    }
    else {
        if( ($width>=$height) && ($height<$size) ) $styles="width:".$size."px;height:auto !important;max-width:100%;";
        else if($height<$size && $width<$size) $styles="";
        else $styles="width:auto !important;max-height:".$size."px;";
        //else if($height>$width) $styles="width:auto !important;max-height:".$size."px;";
    }
    return $styles;
}

function menu_link($link)
{
    if ($link == "" || $link == "#") $linkk = "#";
    else if (check_http($link) == 1) $linkk = $link;
    else if ($link == "ar" || $link == "en") $linkk = asset('/') . $link;
    else if (substr($link, 0, 6) === "public" || substr($link, 0, 7) === "/public" || substr( $link, 0, 7 ) === "\public") $linkk = URL::asset('/') . str_replace(['/public', '\\public'], 'public', $link);
    else if (request()->segment(1) == "ar") $linkk = asset(request()->segment(1)) . "/" . str_replace("ar/", "", $link);
    else $linkk = asset(request()->segment(1)) . "/" . str_replace("en/", "", $link);

    return $linkk;
}

function split_text($text = "", $from = "<ul>", $to = "</ul>")
{
    $startPos = strpos($text, $from);
    $endPos = strpos($text, $to);

    if ($startPos !== false && $endPos !== false) {
        $result = substr($text, $startPos + strlen($from), $endPos - $startPos - strlen($from));
        return $result;
    } else {
        return "No match found.";
    }
}

function check_if_image($filename)
{
    $allowed =  array('gif', 'png', 'jpg', 'jpeg', 'webp', 'svg');
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if (!in_array($ext, $allowed)) {
        return false;
    } else {
        return true;
    }
}

function everything_in_tags($string, $tagname)
{
    preg_match_all('/<' . $tagname . '>(.*?)<\/' . $tagname . '>/s', $string, $matches);
    return $matches[1];
}

function getLocationInfoByIp()
{
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = @$_SERVER['REMOTE_ADDR'];
    $result  = array('country' => '', 'city' => '');
    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }
    $ip_data = \Illuminate\Support\Facades\Cache::remember("geo_ip_{$ip}", 86400, function() use ($ip) {
        $response = @file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip);
        return $response ? json_decode($response) : null;
    });
    if ($ip_data && $ip_data->geoplugin_countryName != null) {
        $result['country'] = $ip_data->geoplugin_countryCode;
        $result['city'] = $ip_data->geoplugin_city;
    }
    return $result;
}

function create_link($id, $title, $type, $lang)
{ //type=1->article/type=2->category//lang=1->Arabic/lang=2->English
    global $web_path;
    $link = $web_path;
    if ($lang == 2) $link .= "en/";
    if ($type == 1) $link .= "article/";
    else if ($type == 2)  $link .= "category/";
    $link .= $id . "/" . friendly_encode($title);

    return $link;
}

function getYoutubeEmbedUrl($url)
{
    $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_-]+)\??/i';
    $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))([a-zA-Z0-9_-]+)/i';

    if (preg_match($longUrlRegex, $url, $matches)) {
        $youtube_id = $matches[count($matches) - 1];
    }

    if (preg_match($shortUrlRegex, $url, $matches)) {
        $youtube_id = $matches[count($matches) - 1];
    }
    return 'https://www.youtube.com/embed/' . $youtube_id;
}

function clean_html($html)
{
    //** Return if string not given or empty.
    if (!is_string($html)|| trim($html) == '')
        return $html;

    return preg_replace('/<(?!\/?(p|div)\b)[^>]*>/', '', $html);
}

function remove_empty_tags_recursive($str, $repto = NULL)
{
    //** Return if string not given or empty.
    if (!is_string($str)|| trim($str) == '')
        return $str;

    //** Recursive empty HTML tags.
    return preg_replace(
        //** Pattern written by Junaid Atari.
        '/<([^<\/>]*)>([\s]*?|(?R))<\/\1>/imsU',
        //** Replace with nothing if string empty.
        !is_string($repto) ? '' : $repto,
        //** Source string
        $str);
}

function remove_style_attr($str, $repto = NULL)
{
    //** Return if string not given or empty.
    if (!is_string($str)|| trim($str) == '')
        return $str;

    return preg_replace('/(<[^>]+) style=".*?"/i', '$1', $str);
}

function isMobile()
{
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

function GetTextFromTo($from = "", $to = "", $text = "")
{
    $first_part = substr($text, strrpos($text, $from) + strlen($from));
    $arr = explode($to, $first_part);
    return $arr[0];
}

function remove_http($url)
{
    $disallowed = array('http://', 'https://');
    foreach ($disallowed as $d) {
        if (strpos($url, $d) === 0) {
            return str_replace($d, '', $url);
        }
    }
    return $url;
}

function distance($lat1, $lon1, $lat2, $lon2, $unit)
{
    //$unit="M","K","N" //for Miles,Kilometers.Nautical Miles
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
        return 0;
    } else {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
}

function get_string_between($string, $start, $end)
{
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function removeDoubleSlashes($url)
{
    // Remove double or more slashes and replace with a single slash
    $url = preg_replace('#/+#', '/', $url);
    // Ensure the protocol part (if present) is not affected
    $url = preg_replace('#:/#', '://', $url, 1);
    return $url;
}

function CleanHtml($Body = "")
{
    $pattern = '/<(a|strong|img|video|iframe)[^>]*>/i';
    $Body = preg_replace($pattern, '', $Body);

    // Pattern to match and remove inline styles within any tag
    $inlineStylePattern = '/\s*style="[^"]*"/i';
    $Body = preg_replace($inlineStylePattern, '', $Body);

    $inlineStylePattern = '/\s*style=("|\')[^"\']*\1/i';
    $Body = preg_replace($inlineStylePattern, '', $Body);

    $inlineStylePattern = '/\s*style=("|\')(.*?)\1/i';
    $Body = preg_replace($inlineStylePattern, '', $Body);

    // Pattern to match and remove <style> tags with their content
    $styleTagPattern = '/<style\b[^>]*>(.*?)<\/style>/is';
    $Body = preg_replace($styleTagPattern, '', $Body);

    return $Body;
}

function cleanExcelText($html = "")
{
    return str_replace('&', '&amp;', $html);
}

function closeHtmlTags($html)
{
    $openedTags = [];
    // Regular expression to find all tags in the HTML
    preg_match_all('/<([a-z]+)(?: .*)?(?<![\/|\/ ])>/iU', $html, $result);
    $openedTags = $result[1];
    // Regular expression to find all closing tags in the HTML
    preg_match_all('/<\/([a-z]+)>/iU', $html, $result2);
    $closedTags = $result2[1];
    // Reverse the order of opened tags
    $openedTags = array_reverse($openedTags);
    foreach ($openedTags as $tag) {
        // If there are more opening tags than closing tags, close the tag
        if (!in_array($tag, $closedTags)) {
            $html .= "</$tag>";
        } else {
            // Remove the tag from the closed tags array
            unset($closedTags[array_search($tag, $closedTags)]);
        }
    }
    return $html;
}

function findTextAroundTags($text, $tag, $class, $contextLength = 10)
{
    // Create the regular expression pattern
    $pattern = '/.{0,' . $contextLength . '}(<' . $tag . ' class="' . $class . '">.*?<\/' . $tag . '>).{0,' . $contextLength . '}/u';
    // Use preg_match_all to find all matches
    preg_match_all($pattern, $text, $matches);
    // Return the matches
    return $matches[0];
}

function convertListItemsToDivs($html)
{
    $html = CheckQuotations(filterBody($html));
    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

    $xpath = new DOMXPath($dom);
    // Find all <li> elements
    $listItems = $xpath->query('//li');

    $newContent = '';

    foreach ($listItems as $li) {
        $icon = $li->getElementsByTagName('i')->item(0);
        $text = $li->getElementsByTagName('span')->item(0);
        // Extract icon class
        $iconClass = $icon ? $icon->getAttribute('class') : '';
        $textContent = $text ? trim($text->textContent) : '';
        // Generate the new div structure
        $newContent .= '<div class="adress-box p-0">';
        if ($iconClass) {
            $newContent .= '<div class="icon-link"><i class="' . $iconClass . '"></i></div>';
        }
        $newContent .= '<div class="address-content">';
        $newContent .= '<h6>' . htmlspecialchars($textContent) . '</h6>';
        $newContent .= '</div></div>';
    }
    // Replace the <li> with the generated <div> content
    $html = preg_replace('/<ul.*?>.*?<\/ul>/s', $newContent, $html);

    return $html;
}

function parseContactList($html) {
    $doc = new DOMDocument();

    // Force UTF-8 encoding
    $html = '<?xml encoding="UTF-8">' . $html;

    // Suppress warnings for invalid HTML
    @$doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    $lis = $doc->getElementsByTagName('li');
    $items = [];

    foreach ($lis as $li) {
        // Keep the inner HTML
        $innerHtml = '';
        foreach ($li->childNodes as $child) {
            $innerHtml .= $li->ownerDocument->saveHTML($child);
        }

        $textCheck = strip_tags($innerHtml); // for detection only

        if (filter_var($textCheck, FILTER_VALIDATE_EMAIL)) {
            $icon = 'fal fa-envelope';
        } elseif (preg_match('/^\+?[0-9\s\-()]+$/', $textCheck)) {
            $icon = 'fal fa-phone';
        } else {
            $icon = 'far fa-map-marker-alt';
        }

        $items[] = [
            'icon' => $icon,
            'html' => $innerHtml, // full HTML preserved
        ];
    }

    return $items;
}