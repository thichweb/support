<?php
if (! function_exists('remove_file')) {
    /**
     * Ham xoa file khoi he thong
     *
     * @param string $pathfile
     * @return bool
     */
    function remove_file($pathfile)
    {
        if (empty($pathfile) || realpath($pathfile) === false) {
            return false;
        }
        if (file_exists($pathfile)) {
            @unlink($pathfile);
        }
        return true;
    }
}

if (! function_exists('get_src_img')) {
    /**
     * Lấy src của một tấm hình
     *
     * @param string $img Link hình
     * @return string
     */
    function get_src_img($img)
    {
        $pattern = '/src=[\'"]?([^\'" >]+)[\'" >]/';
        preg_match($pattern, $img, $link);
        $link = @$link[1];
        $link = urldecode($link);
        return $link;
    }
}

if (! function_exists('create_dir')) {
    /**
     * Tạo thư mục
     *
     * @param string $pathdir : Dường dẫn
     * @param boolean $thumbdir : có tạo thumb ko
     * @return void
     */
    function create_dir($pathdir, $thumbdir = true)
    {
        if (! is_dir($pathdir)) {
            @mkdir($pathdir, 0777, true);
        }
        if ($thumbdir) {
            if (! is_dir($pathdir . "/thumb/")) {
                mkdir($pathdir . "/thumb/", 0777, true);
            }
        }
    }
}

if (! function_exists('delete_dir')) {
    /**
     * ### Xóa thư mục
     *
     * @param string $dirPath
     * @return void
     */
    function delete_dir($dirPath)
    {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath phải là một thư mục");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                delete_dir($file);
            } else {
                @unlink($file);
            }
        }
        rmdir($dirPath);
    }
}

if (! function_exists('remove_file_recursive')) {
    /**
     * Xóa file trong thư mục và tất cả thư mục con
     *
     * @param string $dir Thư mục cần xóa
     * @param string $filename Tên file cần xoa
     * @return void
     */
    function remove_file_recursive($dir, $filename)
    {
        $ffs = scandir($dir);
        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);
        // prevent empty ordered elements
        if (count($ffs) < 1) {
            return;
        }
        foreach ($ffs as $ff) {
            if ($filename == $ff) {
                if (file_exists($dir . '/' . $ff)) {
                    @unlink($dir . '/' . $ff);
                }
            }
            if (is_dir($dir . '/' . $ff)) {
                remove_file_recursive($dir . '/' . $ff, $filename);
            }
        }
    }
}

if (! function_exists('redirect_history')) {
    /**
     * Chuyển hướng về trang trước
     *
     * @return void
     */
    function redirect_history()
    {
        $previous = "javascript:history.go(-1)";
        if (isset($_SERVER['HTTP_REFERER'])) {
            $previous = $_SERVER['HTTP_REFERER'];
        }
        redirect_to($previous);
    }
}

if (! function_exists('redirect_to')) {
    /**
     * Chuyển hướng
     *
     * @param string $url Đường dẫn cần tới
     * @return void
     */
    function redirect_to($url, $delay = false)
    {
        if ($delay) {
            echo "
			<script>
				setTimeout(\"location.href = '" . $url . "';\", " . $delay . ");
			</script>";
        } else {
            echo "<script>location.href = '{$url}';</script>";
        }
    }
}

if (! function_exists('extract_image_from_content')) {
    /**
     * ### Tách hình ảnh ra hỏi nội dung
     *
     * Trả về: mảng hình ảnh
     *
     * @param string $content
     * @return void
     */
    function extract_image_from_content($content)
    {
        preg_match_all('/<img[^>]+>/i', $content, $result);
        return $result;
    }
}

if (! function_exists('delete_image_in_content')) {
    /**
     * ### Xóa tất cả hình ảnh trong nội dung
     *
     * @param string $noiDung
     * @param string $pathToFile
     * @param string $domain
     * @return void
     */
    function delete_image_in_content($noiDung, $pathToFile, $domain = null)
    {
        if ($domain === null) {
            $domain = \Thichweb\Session::get('ROOT_PATH');
        }
        $result = null;
        $imgs = (array) extract_image_from_content($noiDung);
        if (count($imgs) > 0) {
            foreach ($imgs as $img) {
                for ($i = 0; $i < count($img); $i++) {
                    $path = get_src_img($img[$i]);
                    $path = str_replace($domain, '', $path);
                    $path = $pathToFile . $path;
                    $result .= $path;
                    @unlink($path);
                }
            }
        }
    }
}

if (! function_exists('is_refresh')) {
    /**
     * ### Kiểm tra trang web đang refresh
     *
     * @return boolean
     */
    function is_refresh()
    {
        $pageRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && ($_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0' ||  $_SERVER['HTTP_CACHE_CONTROL'] == 'no-cache');
        return $pageRefreshed == 1 ? true : false;
    }
}

if (! function_exists('is_dir_empty')) {
    /**
     * Thư mục có rỗng hay không
     *
     * @param string $dir
     * @return boolean
     */
    function is_dir_empty($dir)
    {
        if (! is_readable($dir)) {
            return null;
        }
        return (count(scandir($dir)) == 2);
    }
}

if (! function_exists('set_tag_image')) {
    /**
     * ### Thêm Title và Alt cho hình ảnh trong nội dung
     *
     * @param string $content: Nội dung cần tìm
     * @param string $title: tiêu đề thay thế
     * @return void
     */
    function set_tag_image($content, $title)
    {
        if (! $content) {
            return null;
        }
        // Bóc tách hình ảnh ra khỏi nội dung
        preg_match_all('/<img[^>]+>/i', $content, $result);
        $arrimg = $result[0];
        $ret = null;
        if (count($arrimg) > 0) {
            // Chạy vòng lặp lấy các thẻ img
            $doc = new DOMDocument();
            $doc->loadHTML($content);
            $xml = simplexml_import_dom($doc); // just to make xpath more simple
            $images = $xml->xpath('//img');
            foreach ($images as $kimg => $img) {
                $alt      = $img['alt'];
                $titleimg = $img['title'];
                $retAlt = null;
                // THAY THẾ ALT
                if (! $alt || $alt == '' || $alt == "" || is_null($alt)) {
                    $retAlt = str_replace('alt=""', '', $arrimg[$kimg]);
                    $retAlt = str_replace("alt=''", '', $retAlt);
                    $retAlt = str_replace("alt", '', $retAlt);
                    $retAlt = str_replace('<img', '<img alt="' . $title . '" ', $arrimg[$kimg]);
                } else {
                    $retAlt = $arrimg[$kimg];
                }
                // THAY THẾ TITLE
                if (! $titleimg || $titleimg == '' || $titleimg == "" || is_null($titleimg)) {
                    $retAlt = str_replace('title=""', '', $retAlt);
                    $retAlt = str_replace("title=''", '', $retAlt);
                    $retAlt = str_replace("title", '', $retAlt);
                    $retAlt = str_replace('<img', '<img title="' . $title . '" ', $retAlt);
                }
                $content = str_replace($arrimg[$kimg], $retAlt, $content);
            }
        }
        return $content;
    }
}

if (! function_exists('get_client_ip')) {
    /**
     * Lấy IP hiện tại
     *
     * @return void
     */
    function get_client_ip()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        } elseif (getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }
}

if (! function_exists('truncate_number')) {
    /**
     * ## Làm tròn số
     *
     * @param int $number
     * @param integer $precision
     * @return void
     */
    function truncate_number($number, $precision = 2)
    {
        // Zero causes issues, and no need to truncate
        if (0 == (int) $number) {
            return $number;
        }
        // Are we negative?
        $negative = $number / abs($number);
        // Cast the number to a positive to solve rounding
        $number = abs($number);
        // Calculate precision number for dividing / multiplying
        $precision = pow(10, $precision);
        // Run the math, re-applying the negative value to ensure returns correctly negative / positive
        return floor($number * $precision) / $precision * $negative;
    }
}

if (! function_exists('download_page')) {
    /**
     * ### Lấy nội dung từ url
     *
     * @param string $path
     * @return void
     */
    function download_page($path)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $path);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $retValue = curl_exec($ch);
        curl_close($ch);
        return $retValue;
    }
}

if (! function_exists('lay_noidung_json')) {
    /**
     * ### Hàm lấy nội dung file json Online
     *
     * @param string $url
     * @param boolean $passArray
     * @return void
     */
    function lay_noidung_json($url, $passArray = true)
    {
        if (empty($url)) {
            return null;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($passArray) {
            return json_decode($result, true);
        } else {
            return $result;
        }
    }
}

if (! function_exists('get_usd_currency')) {
    /**
     * ### Lấy tỷ giá hiện tại: từ VND sang USD
     *
     * @return void
     */
    function get_usd_currency($CurrencyCode = 'USD')
    {
        // Fetching JSON
        $req_url = 'https://portal.vietcombank.com.vn/Usercontrols/TVPortal.TyGia/pXML.aspx';
        $sXML    = download_page($req_url);
        $xml     = simplexml_load_string($sXML);
        $json    = json_encode($xml);
        $arr     = json_decode($json, true);
        $exrate  = $arr['Exrate'];

        $currency_rates = null;
        foreach ($exrate as $kex => $exr) {
            $tmp_array = $exr['@attributes'];
            if ($tmp_array['CurrencyCode'] == $CurrencyCode) {
                $currency_rates = $tmp_array['Transfer'];
            }
        }
        $currency_rates = str_replace(",", "", $currency_rates);

        return $currency_rates;
    }
}

if (! function_exists('vnd_to_currency')) {
    /**
     * Chuyển tiền VNĐ sang ngoại tệ (Currency)
     *
     * @param integer $VND
     * @return void
     */
    function vnd_to_currency($VND = 0, $CurrencyCode = 'USD')
    {
        $USD_price = 0;
        if ($VND) {
            // Fetching JSON
            $currency_rates = get_usd_currency($CurrencyCode);

            // Continuing if we got a result
            if (! empty($currency_rates)) {
                try {
                    $USD_price = round(($VND / $currency_rates), 2);
                } catch (Exception $e) {
                    // Handle JSON parse error...
                }
            }

            return $USD_price;
        }
        return false;
    }
}

if (! function_exists('exec_post_request')) {
    /**
     * Gửi dữ liệu POST qua Curl
     *
     * @param string $url
     * @param mixed $data
     * @return void
     */
    function exec_post_request($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }
}

if (! function_exists('send_telegram')) {
    /**
     * ### Gửi tin nhắn qua telegram
     *
     * @param string $messageTXT
     * @param string $userID
     * @param string $tokenBOT
     * @return void
     */
    function send_telegram($messageTXT, $userID, $tokenBOT)
    {
        $url = "https://api.telegram.org/bot" . $tokenBOT . "/sendMessage?chat_id=" . $userID;
        $url = $url . "&text=" . urlencode($messageTXT);
        $ch = curl_init();
        $optArray = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true
    );
        curl_setopt_array($ch, $optArray);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}

if (! function_exists('tao_thongdiep')) {
    /**
     * Hiện thị thông điệp tức thời lên hệ thống
     *
     * @param string $thongdiep Thông điệp muốn hiện thị
     * @param string $loai Loại thông báo succes|error
     * @return void
     */
    function tao_thongdiep($thongdiep, $loai = "success")
    {
        \Thichweb\Session::flash('notification', [
            'status' => $loai,
            'message' => $thongdiep
        ]);
    }
}

if (! function_exists('get_field_table')) {
    /**
     * Lấy tất cả field của bảng
     *
     * @param string $table Tên bảng cần lấy
     * @param string|array $except_field Tên các field ko cần lấy
     * @return array
     */
    function get_field_table($table, $except_field = null)
    {
        if (blank($table)) {
            return;
        }

        if (! MysqliDb::getInstance()->tableExists($table)) {
            dd("Table [{$table}] Không tồn tại");
            return;
        }

        // Xử lý bóc tách để lấy mảng các Field
        $cribe = MysqliDb::getInstance()->rawQuery("DESCRIBE {$table}");
        $arrField = \Thichweb\Arr::pluck($cribe, 'Field');

        // Xử lý và loại bỏ các field_except ra khỏi
        // Có 2 Người dùng có thể truyền mảng hoặc chuỗi cách nhau dấu phẩy
        $arrtmp = [];

        // Nếu field_except là Mảng
        if (is_array($except_field) && collect($except_field)->filter()->count()) {
            foreach ($except_field as $field) {
                if (is_string($field) && field_exists($table, $field)) {
                    array_push($arrtmp, collect($arrField)->search($field));
                }
            }
            $except_field = $arrtmp;
        } elseif (is_string($except_field) && filled($except_field)) {
            // Nếu field_except là Chuỗi
            if (\Thichweb\Str::contains($except_field, ",")) {
                // Nếu field_except là chuỗi nối(ngăn) bởi dấu phẩy
                $arrstrfield = \Thichweb\Str::of($except_field)->explode(",")->all();
                if (collect($arrstrfield)->filter()->count()) {
                    foreach ($arrstrfield as $field) {
                        $field = trim($field);
                        if (is_string($field)) {
                            if (collect($arrField)->contains($field) && field_exists($table, $field)) {
                                array_push($arrtmp, collect($arrField)->search($field));
                            }
                        }
                    }
                }
                $except_field = $arrtmp;
            } elseif (field_exists($table, $except_field)) {
                // Nếu field_except là chuỗi đơn
                $except_field = collect($arrField)->search($except_field);
            }
        }

        $arrField = collect($arrField)->except(
            $except_field
        )->values()->all();

        return $arrField;
    }
}

if (! function_exists('field_exists')) {
    /**
     * Kiểm tra field có tồn tại trong bảng hay không
     *
     * @param string $table
     * @param string $fieldname
     * @return bool
     */
    function field_exists($table, $fieldname)
    {
        if (blank($table) || blank($fieldname)) {
            return false;
        }
        return filled(MysqliDb::getInstance()->rawQueryValue("SHOW COLUMNS FROM `{$table}` LIKE '{$fieldname}'"));
    }
}

if (! function_exists('num_to_letters')) {
    /**
     * Chuyển số thành chữ
     *
     * @uses Sử_dụng 1 = A | 2 = B | 3 = C | 27 = AA | 1234567789 = CYWOQRM
     *
     * @param int $n Số cần chuyển
     * @return void
     */
    function num_to_letters($n)
    {
        $n -= 1;
        for ($r = ""; $n >= 0; $n = intval($n / 26) - 1) {
            $r = chr($n % 26 + 0x41) . $r;
        }
        return $r;
    }
}

if (! function_exists('letters_to_num')) {
    /**
     * Chuyển chữ thành số
     *
     * @uses Sử_dụng A = 1 | B = 2 | C = 3 | AA = 27 | CYWOQRM = 1234567789
     *
     * @param string $a chữ cần chuyển
     * @return void
     */
    function letters_to_num($a)
    {
        $l = strlen($a);
        $n = 0;
        for ($i = 0; $i < $l; $i++) {
            $n = $n * 26 + ord($a[$i]) - 0x40;
        }
        return $n;
    }
}

if (! function_exists('get_device')) {
    /**
     * Hàm nhận biết thiết bị hiện tại
     *
     * @return string (mobile | tablet | mobiles | unknow)
     */
    function get_device()
    {
        $device = 'unknow';
        $detect = new \Thichweb\MobileDetect();
        if ($detect->isMobile() && ! $detect->isTablet()) {
            $device = 'mobile';
        } elseif ($detect->isTablet()) {
            $device = 'tablet';
        } elseif ($detect->isMobile()) {
            $device = 'mobiles';
        }

        return $device;
    }
}

if (! function_exists('get_os')) {
    /**
     * Hàm nhận biết Hệ điều hành của thiết bị di động
     *
     * @return string ( 'ios' | 'android' | 'unknow' )
     */
    function get_os()
    {
        $os = 'unknow';
        $detect = new \Thichweb\MobileDetect();

        if ($detect->isiOS()) {
            $os = 'ios';
        } elseif ($detect->isAndroidOS()) {
            $os = 'android';
        }

        return $os;
    }
}
