<?php
namespace mail;

class Myimap{

    public $username="";
    public $userpwd="";
    public $hostname="";
    public $port=0;
    public $connection=0;//是否连接
    public $state="DISCONNECTED";//连接状态
    public $greeting="";
    public $must_update=0;
    public $inStream=0;

    public function open() {
        if ($this->port==110)
            $this->inStream=imap_open("{{$this->hostname}:110}inbox",$this->username,$this->userpwd);
        else
            $this->inStream=imap_open("{{$this->hostname}:143}inbox",$this->username,$this->userpwd);

        if ($this->inStream) {
            echo "用户：$this->username 的信箱连接成功。<br>";
            return $inStream;
        }
        else {
            echo "用户：$this->username 的信箱连接失败。<br>";
            return 0;
        }
    }

    public function close() {
        if(imap_close($this->inStream)) {
            echo "<hr>已经与服务器 $this->hostname 断开连接。";
            return 1;
        }
        else {
            echo "<hr>与服务器 $this->hostname 断开连接失败。";
            return 0;
        }
    }

    public function CheckMailbox() {
        $mboxinfo=@imap_mailboxmsginfo($this->inStream);
        if ($mboxinfo)
            if ($mboxinfo->Nmsgs>0) {
                echo "您的收件箱里共有邮件数：".$mboxinfo->Nmsgs."<br>";
                echo "未读邮件数：".$mboxinfo->Unread."<br>";
                echo "新进邮件数：".$mboxinfo->Recent."<br>";
                echo "总共占用空间：".$mboxinfo->Size."字节<br>";
                echo "最新邮件日期：".$mboxinfo->Date."<br><hr>";
            }
            else {
                echo "您的信箱里没有邮件。<br><hr>";
            }
        else {
            echo '<font color="RED">错误：无法获取收件箱的信息。</font>';
        }

        echo '<table border="1">';
        $sort_reverse=1;
        $sorted = imap_sort($this->inStream, SORTDATE, $sort_reverse, SE_UID);

        for ($i=1;$i<=$mboxinfo->Nmsgs;$i++) {
            $msgHeader = @imap_header($this->inStream, imap_msgno($this->inStream, $sorted[$i-1]));
            dump($msgHeader);
            //日期
            if (isset($msgHeader->date)) {
                $date = $msgHeader->date;
                if (ord($date) > 64)
                    $date = substr($date, 5);
                    while (strstr('', $date)) {
                        $date = str_replace('', ' ', $date);
                    }
            }

            if (isset($msgHeader->from[0])) {
                $from = $msgHeader->from[0];
                if (isset($from->personal)) {
                    $frm = trim($this->decode_mime_string($from->personal));
                }elseif (isset($from->mailbox) && isset($from->host)) {
                        $frm = $from->mailbox . '@' . $from->host;
                }elseif (isset($msgHeader->fromaddress)){
                        $frm = trim($this->decode_mime_string($h->fromaddress));
                }
            }elseif (isset($msgHeader->fromaddress)){
                $frm = trim($this->decode_mime_string($msgHeader->fromaddress));
            }

            if (isset($msgHeader->toaddress))
                $to = trim($msgHeader->toaddress);
            else
                $to = "未知";

            if (isset($msgHeader->subject))
                $sub = trim($this->decode_mime_string($msgHeader->subject));
            if (isset($msgHeader->Size))
                $msg_size = ($msgHeader->Size > 1024) ? sprintf("%.0f kb", $msgHeader->Size / 1024) : $msgHeader->Size;
            if (strlen($frm) > 50)
                $frm = mb_substr($frm, 0, 50) . '...';
            if (strlen($sub) > 50)
                $sub = mb_substr($sub, 0, 50) . '...';
            echo '<tr>';
            echo '<td align="center"><input type="checkbox" name="check"></td><td>'.$frm.'</td><td><a href="showbody_imap.php?usr='.$this->username.'&pwd='.$this->userpwd.'&msg='.$i.'">'.$sub.'</a></td><td>'.$date.'</td><td>'.$msg_size.'</td>';
            echo '</tr>';
        }
        echo "</table>";
    }

    /**
     * 获取到某一封邮箱的详细信息
     * @Author Foggy
     * @Date   2018-12-12
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $msgid [description]
     * @return [type]                      [description]
     */
    public function getOne($msgid) {  
        $body = $this->getPart($msgid, "TEXT/HTML");  
        if ($body == '') {  
            $body = $this->getPart($msgid, "TEXT/PLAIN");  
        }  
        if ($body == '') {  
            return '';  
        }  
        return $body;  
    }

    /**
     * 将某一封邮件标记为已读
     * @Author Foggy
     * @Date   2018-12-12
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $msgid [description]
     * @return [type]                      [description]
     */
    public function mailRead($msgid) {  
        $status = imap_setflag_full($this->inStream, $msgid, "\\Seen");  
        return $status;  
    }

    /**
     * 删除指定的邮件信息
     * @Author Foggy
     * @Date   2018-12-12
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $msgid [description]
     * @return [type]                      [description]
     */
    public function mailDelete($msgid) {  
        imap_delete($this->inStream, $msgid);  
    }

    /** 
     * get attach of the message 
     * 
     * @param string $msgCount 
     * @param string $path 
     * @return array 
     */
    public function getAttach($msgid, $path) {  
        $struckture = imap_fetchstructure($this->inStream, $msgid);  
        $attach = array();  
        if ($struckture->parts) {  
            foreach ($struckture->parts as $key => $value) {  
                $encoding = $struckture->parts[$key]->encoding;  
                if ($struckture->parts[$key]->ifdparameters) {  
                    $name = $struckture->parts[$key]->dparameters[0]->value;  
                    $message = imap_fetchbody($this->inStream, $msgid, $key + 1);  
                    if ($encoding == 0) {  
                        $message = imap_8bit($message);  
                    } else if ($encoding == 1) {  
                        $message = imap_8bit($message);  
                    } else if ($encoding == 2) {  
                        $message = imap_binary($message);  
                    } else if ($encoding == 3) {  
                        $message = imap_base64($message);  
                    } else if ($encoding == 4) {  
                        $message = quoted_printable_decode($message);  
                    }  
                    $this->downAttach($path, $name, $message);  
                    $attach[] = $name;  
                }  
                if ($struckture->parts[$key]->parts) {  
                    foreach ($struckture->parts[$key]->parts as $keyb => $valueb) {  
                        $encoding = $struckture->parts[$key]->parts[$keyb]->encoding;  
                        if ($struckture->parts[$key]->parts[$keyb]->ifdparameters) {  
                            $name = $struckture->parts[$key]->parts[$keyb]->dparameters[0]->value;  
                            $partnro = ($key + 1) . "." . ($keyb + 1);  
                            $message = imap_fetchbody($this->inStream, $msgid, $partnro);  
                            if ($encoding == 0) {  
                                $message = imap_8bit($message);  
                            } else if ($encoding == 1) {  
                                $message = imap_8bit($message);  
                            } else if ($encoding == 2) {  
                                $message = imap_binary($message);  
                            } else if ($encoding == 3) {  
                                $message = imap_base64($message);  
                            } else if ($encoding == 4) {  
                                $message = quoted_printable_decode($message);  
                            }  
                            $this->downAttach($path, $name, $message);  
                            $attach[] = $name;  
                        }  
                    }  
                }  
            }  
        }  
        return $attach;  
    }  
  
    /** 
     * download the attach of the mail to localhost 
     * 
     * @param string $filePath 
     * @param string $message 
     * @param string $name 
     */  
    public function downAttach($filePath, $name, $message) {  
        if (is_dir($filePath)) {  
            $fileOpen = fopen($filePath . $name, "w");  
        } else {  
            mkdir($filePath);  
        }  
        fwrite($fileOpen, $message);  
        fclose($fileOpen);  
    }  
  
    /** 
     * click the attach link to download the attach 
     * 
     * @param string $id 
     */  
    public function getAttachData($id, $filePath, $fileName) {  
        $nameArr = explode('.', $fileName);  
        $length = count($nameArr);  
        $contentType = $this->_contentType[$nameArr[$length - 1]];  
        if (!$contentType) {  
            $contentType = $this->_contentType['*'];  
        }  
        $filePath = chop($filePath);  
        if ($filePath != '') {  
            if (substr($filePath, strlen($filePath) - 1, strlen($filePath)) != '/') {  
                $filePath .= '/';  
            }  
            $filePath .= $fileName;  
        } else {  
            $filePath = $fileName;  
        }  
        if (!file_exists($filePath)) {  
            echo 'the file is not exsit';  
            return false;  
        }  
        $fileSize = filesize($filePath);  
        header("Content-type: " . $contentType);  
        header("Accept-Range : byte ");  
        header("Accept-Length: $fileSize");  
        header("Content-Disposition: attachment; filename=" . $fileName);  
        $fileOpen = fopen($filePath, "r");  
        $bufferSize = 1024;  
        $curPos = 0;  
        while (!feof($fileOpen) && $fileSize - $curPos > $bufferSize) {  
            $buffer = fread($fileOpen, $bufferSize);  
            echo $buffer;  
            $curPos += $bufferSize;  
        }  
        $buffer = fread($fileOpen, $fileSize - $curPos);  
        echo $buffer;  
        fclose($fileOpen);  
        return true;  
    } 

    private function getPart($msgid, $mimeType, $structure = false, $partNumber = false) {  
  
        if (!$structure) {  
            $structure = imap_fetchstructure($this->inStream, $msgid);  
        }  
        if ($structure) {  
            if ($mimeType == $this->getMimeType($structure)) {  
                if (!$partNumber) {  
                    $partNumber = "1";  
                }  
                $fromEncoding = $structure->parameters[0]->value;  
                $text = imap_fetchbody($this->inStream, $msgid, $partNumber);  
                if ($structure->encoding == 3) {  
                    $text = imap_base64($text);  
                } else if ($structure->encoding == 4) {  
                    $text = imap_qprint($text);  
                }  
                $text = mb_convert_encoding($text, 'utf-8', $fromEncoding);  
                return $text;  
            }  
            if ($structure->type == 1) {  
                while (list($index, $subStructure) = each($structure->parts)) {  
                    $prefix = '';  
                    if ($partNumber) {  
                        $prefix = $partNumber . '.';  
                    }  
                    $data = $this->getPart($msgid, $mimeType, $subStructure, $prefix . ($index + 1));  
                    if ($data) {  
                        return $data;  
                    }  
                }  
            }  
        }  
        return false;  
    }

    private function getMimeType($structure) {  
        $mimeType = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");  
        if ($structure->subtype) {  
            return $mimeType[(int) $structure->type] . '/' . $structure->subtype;  
        }  
        return "TEXT/PLAIN";  
    }

    public function decode_mime_string ($string) {
        $step1 = str_replace('?=', '', $string);
        $step2 = str_replace('=?UTF-8?B?', '', $step1);
        return base64_decode($step2);
    }

    // public function decode_mime_string ($string) {
    //     $pos = strpos($string, '=?');
    //     if (!is_int($pos)) {
    //         return $string;
    //     }

    //     $preceding = substr($string, 0, $pos); // save any preceding text

    //     $search = substr($string, $pos+2, 75); // the mime header spec says this is the longest a single encoded word can be
    //     $d1 = strpos($search, '?');
    //     if (!is_int($d1)) {
    //         return $string;
    //     }

    //     $charset = substr($string, $pos+2, $d1);
    //     $search = substr($search, $d1+1);

    //     $d2 = strpos($search, '?');
    //     if (!is_int($d2)) {
    //         return $string;
    //     }

    //     $encoding = substr($search, 0, $d2);
    //     $search = substr($search, $d2+1);

    //     $end = strpos($search, '?=');
    //     if (!is_int($end)) {
    //         return $string;
    //     }

    //     $encoded_text = substr($search, 0, $end);dump($encoded_text);
    //     $rest = substr($string, (strlen($preceding . $charset . $encoding . $encoded_text)+6));

    //     switch ($encoding) {
    //     case 'Q':
    //     case 'q':
    //         $encoded_text = str_replace('_', '%20', $encoded_text);
    //         $encoded_text = str_replace('=', '%', $encoded_text);
    //         $decoded = urldecode($encoded_text);
    //         break;

    //     case 'B':
    //     case 'b':
    //         $decoded = urldecode(base64_decode($encoded_text));
    //         break;

    //     default:
    //         $decoded = '=?' . $charset . '?' . $encoding . '?' . $encoded_text . '?=';
    //         break;
    //     }

    //     return $preceding . $decoded . $this->decode_mime_string($rest);
    // }

    public function display_toaddress ($user, $server, $from) {
        return is_int(strpos($from, $this->get_barefrom($user, $server)));
    }

    public function get_barefrom($user, $server) {
        $barefrom = "$user@$real_server";

        return $barefrom;
    }

    public function get_structure($msg_num) {
        $structure=imap_fetchstructure($this->inStream,$msg_num);
        //echo gettype($structure);
        return $structure;
    }

    public function proc_structure($msg_part, $part_no, $msg_num) {
        if ($msg_part->ifdisposition) {
            // See if it has a disposition
            // The only thing I know of that this
            // would be used for would be an attachment
            // Lets check anyway
            if ($msg_part->disposition == "ATTACHMENT") {
                // If it is an attachment, then we let people download it
                // First see if they sent a filename
                $att_name = "unknown";
                for ($lcv = 0; $lcv <count($msg_part->parameters); $lcv++) {
                    $param = $msg_part->parameters[$lcv];

                    if ($param->attribute == "NAME") {
                        $att_name = $param->value;
                        break;
                    }
                }

                // You could give a link to download the attachment here....
                echo '<a href="'.$att_name.'">'.$att_name.'</a><br>';
                $fp=fopen(".$att_name","w+");
                fputs($fp,imap_base64(imap_fetchbody($this->inStream,$msg_num,$part_no)));
                fclose($fp);
            }
            else {
            // I guess it is used for something besides attachments????
            }
        }
        else {
            // Not an attachment, lets see what this part is...
            switch ($msg_part->type) {
            case TYPETEXT:
                $mime_type = "text";
                break;
            case TYPEMULTIPART:
                $mime_type = "multipart";
                // Hey, why not use this function to deal with all the parts
                // of this multipart part :)
                for ($i = 0; $i <count($msg_part->parts); $i++) {
                    if ($part_no != "") {
                        $part_no = $part_no.".";
                    }
                    for ($i = 0; $i <count($msg_part->parts); $i++) {
                        $this->proc_structure($msg_part->parts[$i], $part_no.($i + 1), $msg_num);
                    }
                }
                break;
            case TYPEMESSAGE:
                $mime_type = "message";
                break;
            case TYPEAPPLICATION:
                $mime_type = "application";
                break;
            case TYPEAUDIO:
                $mime_type = "audio";
                break;
            case TYPEIMAGE:
                $mime_type = "image";
                break;
            case TYPEVIDEO:
                $mime_type = "video";
                break;
            case TYPEMODEL:
                $mime_type = "model";
                break;
            default:
                $mime_type = "unknown";
                // hmmm....
            }

            $full_mime_type = $mime_type."/".$msg_part->subtype;
            //echo $full_mime_type.'<hr>';

            // Decide what you what to do with this part
            // If you want to show it, figure out the encoding and echo away
            switch ($msg_part->encoding) {
            case ENCBASE64:
                // use imap_base64 to decode
                $fp=fopen(".$att_name","w+");
                fputs($fp,imap_base64(imap_fetchbody($this->inStream,$msg_num,$part_no)));
                fclose($fp);
                break;
            case ENCQUOTEDPRINTABLE:
                // use imap_qprint to decode
                //echo ereg_replace("n","<br>",imap_qprint(imap_fetchbody($this->inStream,$msg_num,$part_no)));
                echo '<pre>'.imap_qprint(imap_fetchbody($this->inStream,$msg_num,$part_no)).'</pre>';
                break;
            case ENCOTHER:
                // not sure if this needs decoding at all
                break;
            default:
            }
        }
    }
}