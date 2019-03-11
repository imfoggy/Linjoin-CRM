<?php
namespace mail;
use think\Controller;

class Myimap extends Controller{

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
            $this->inStream=@imap_open("{{$this->hostname}:110}inbox",$this->username,$this->userpwd);
        else
            $this->inStream=@imap_open("{{$this->hostname}:993/imap/ssl}inbox",$this->username,$this->userpwd);

        if(!$this->inStream){
            $this->error(imap_last_error());
        }
        
        return $this->inStream;
    }

    public function close() {
        if(imap_close($this->inStream)) {
            //echo "<hr>已经与服务器 $this->hostname 断开连接。";
            //return 1;
            $this->success('已经与服务器'.$this->hostname.'断开连接。');
        }
        else {
            //echo "<hr>与服务器 $this->hostname 断开连接失败。";
            //return 0;
            $this->error('与服务器'.$this->hostname.'断开连接失败。');
        }
    }

    /** 
     * 获取邮件列表(不好用，用下面的listMessages方法来实现)
     * 
     * @param string $msgRange，为空则取出所有的，"1:4"代表取第一条到第四条
     * @return array 
     */  
    public function mailList($msgRange = '') {  
        if ($msgRange) {  
            $range = $msgRange;  
        } else {  
            $this->mailTotalCount();  
            $range = "1:" . $this->_totalCount;  
        }

        //防止越界。
        $maxCount = $this->mailTotalCount();
        $ex = explode(':', $range);
        if($ex[0] > $maxCount){
            $ex[0] = $maxCount;
        }
        if($ex[1] > $maxCount){
            $ex[1] = $maxCount;
        }

        $range = implode(':', $ex);
        $overview = imap_fetch_overview($this->inStream, $range);  
        foreach ($overview as $val) {  
            $mailList[$val->msgno] = (array) $val;  
        }

        $mailList = array_reverse($mailList);

        $lists = [];

        if(count($mailList) > 0){
            $sort_reverse=1;
            $sorted = imap_sort($this->inStream, SORTDATE, $sort_reverse, SE_UID);

            foreach($mailList as $k=>$v){
                $msgHeader = @imap_headerinfo($this->inStream, imap_msgno($this->inStream, $sorted[$k]));
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

                $msgHeader->mydata['fromname'] = $frm;
                $msgHeader->mydata['fromemail'] = $msgHeader->from['0']->mailbox.'@'.$msgHeader->from['0']->host;
                $msgHeader->mydata['title'] = $sub;
                $msgHeader->mydata['sizeFormat'] = $msg_size;
                $msgHeader->mydata['receiveTime'] = date('Y-m-d H:i:s', $msgHeader->udate);
                $msgHeader->mydata['seen'] = $v['seen'];    //seen 1-已读，0-未读。
                $msgHeader->mydata['flagged'] = $v['flagged'];
                $msgHeader->mydata['msgno'] = trim($msgHeader->Msgno);
                $msgHeader->attach = $this->getAttach(imap_msgno($this->inStream, $sorted[$k]), './');
                $lists[] = $msgHeader;
            }

        }

        return $lists;
    }

    /**
     * 分页获取邮箱中的数据
     * @Author Foggy
     * @Date   2018-12-15
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  integer           $page     [description]
     * @param  integer           $per_page [description]
     * @param  [type]            $sort     [description]
     * @return [type]                      [description]
     */
    public function listMessages($page = 1, $per_page = 25, $sort = null){
        $limit = ($per_page * $page);
        $start = ($limit - $per_page) + 1;
        $start = ($start < 1) ? 1 : $start;
        $limit = (($limit - $start) != ($per_page-1)) ? ($start + ($per_page-1)) : $limit;
        $info = imap_check($this->inStream);
        $limit = ($info->Nmsgs < $limit) ? $info->Nmsgs : $limit;

        if(true === is_array($sort)){
            $sorting = array(
               'direction'=> array( 'asc' => 0, 'desc' => 1),
               'by'=> array('date' => SORTDATE, 'arrival' => SORTARRIVAL,'from' => SORTFROM, 'subject' => SORTSUBJECT, 'size' => SORTSIZE)
            );
            $by = (true === is_int($by = $sorting['by'][$sort[0]])) ? $by : $sorting['by']['date'];
            $direction = (true === is_int($direction = $sorting['direction'][$sort[1]])) ? $direction : $sorting['direction']['desc'];
            $sorted = imap_sort($this->inStream, $by, $direction);
            $msgs = array_chunk($sorted, $per_page);
            $msgs = $msgs[$page-1];
        }else{
            $msgs = range($start, $limit); //just to keep it consistent
        }

        $overview = imap_fetch_overview($this->inStream, implode($msgs, ','));

        if(false === is_array($overview)) return false;

        foreach ($overview as $val) {  
            $result[$val->msgno] = (array) $val;  
        }

        $result = array_reverse($result);

        foreach ($result as $k => $r){
            $msgHeader = @imap_headerinfo($this->inStream, $r['msgno']);
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

            $msgHeader->mydata['fromname'] = $frm;
            $msgHeader->mydata['fromemail'] = $msgHeader->from['0']->mailbox.'@'.$msgHeader->from['0']->host;
            $msgHeader->mydata['title'] = $sub;
            $msgHeader->mydata['sizeFormat'] = $msg_size;
            $msgHeader->mydata['receiveTime'] = date('Y-m-d H:i:s', $msgHeader->udate);
            $msgHeader->mydata['seen'] = $r['seen'];    //seen 1-已读，0-未读。
            $msgHeader->mydata['flagged'] = $r['flagged'];
            $msgHeader->mydata['msgno'] = trim($msgHeader->Msgno);
            $msgHeader->attach = $this->getAttach($r['msgno'], './');
            $lists[] = $msgHeader;
        }

        $return = array(
            'res' => $lists,
            'start' => $start,
            'limit' => $limit,
            'sorting' => array('by' => $sort[0], 'direction' => $sort[1]),
            'total' => imap_num_msg($this->inStream)
        );

        $return['pages'] = ceil($return['total'] / $per_page);

        return $return;
    }

    public function getBody($mid){
        if(!$this->inStream){
            return false;
        }
        $body = $this->getPart($this->inStream, $mid, "TEXT/HTML");
        if ($body == ""){
            $body = $this->getPart($this->inStream, $mid, "TEXT/PLAIN");
        }
        if ($body == ""){
            return "";
        }
        return $body;
    }
    /**
     * 获取到邮件的头部信息
     * @Author Foggy
     * @Date   2018-12-13
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $msgid [description]
     * @return [type]                   [description]
     */
    public function getHeader($msgid){
        $info = [];
        $msgHeader = @imap_headerinfo($this->inStream, $msgid);
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

        $info['fromname'] = $frm;
        $info['fromemail'] = $msgHeader->from['0']->mailbox.'@'.$msgHeader->from['0']->host;
        $info['title'] = $sub;
        $info['sizeFormat'] = $msg_size;
        $info['receiveTime'] = date('Y-m-d H:i:s', $msgHeader->udate);
        $info['seen'] = $v['seen'];    //seen 1-已读，0-未读。
        $info['flagged'] = $v['flagged'];
        $info['msgno'] = trim($msgHeader->Msgno);
        $info['attach'] = $this->getAttach($info['msgno'], './');
        return $info;
    }

     /** 
     * get the total count of the current mailbox 
     * 
     * @return int 
     */  
    public function mailTotalCount() {  
        $check = imap_check($this->inStream);  
        $this->_totalCount = $check->Nmsgs;  
        return $this->_totalCount;  
    }

    /**
     * 未读
     * @Author Foggy
     * @Date   2018-12-15
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function getUnseen(){
        $emails = [];
        $emails = imap_search($this->inStream, 'unseen');
        return array_reverse($emails);
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
     * 将一封邮件标记为红旗邮件mailCancelFlagged
     * @Author Foggy
     * @Date   2018-12-14
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $msgid [description]
     * @return [type]                   [description]
     */
    public function mailFlagged($msgid){
        $status = imap_setflag_full($this->inStream, $msgid, "\\Flagged");  
        return $status;
    }

    public function mailCancelFlagged($msgid){
        $status = imap_clearflag_full($this->inStream, $msgid, "\\Flagged");  
        return $status;
    }

    /**
     * 删除
     * @Author Foggy
     * @Date   2018-12-14
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $msgid [description]
     * @return [type]                   [description]
     */
    public function delete($msgid){
        $status = imap_setflag_full($this->inStream, $msgid, "\\Deleted");  
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
     * 邮件移动到草稿箱
     * @Author Foggy
     * @Date   2018-12-13
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $msgid [description]
     * @return [type]                   [description]
     */
    public function draftMail($msgid){
        $status = imap_setflag_full($this->inStream, $msgid, "\\Draft");  
        return $status;
    }

    /**
     * [downloadAttach description]
     * @Author Foggy
     * @Date   2018-12-14
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $msgid [description]
     * @param  [type]            $path  [description]
     * @return [type]                   [description]
     */
    public function downloadAttachment($msgid, $path){
        $struckture = imap_fetchstructure($this->inStream, $msgid);
        $attach = array();
        $newname = date('YmdHis').rand(10000,99999).'-';
        if ($struckture->parts) {  
            foreach ($struckture->parts as $key => $value) {  
                $encoding = $struckture->parts[$key]->encoding;  
                if ($struckture->parts[$key]->ifdparameters) {  
                    $name = $newname.$struckture->parts[$key]->dparameters[0]->value;  
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
                            $name = $newname.$struckture->parts[$key]->parts[$keyb]->dparameters[0]->value;  
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
     * 直接保存邮件中的附件到某个位置。
     * 
     * @param string $msgid [信息的标识] 
     * @param string $path [附近保存位置，如'./attach'];
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
                    //$this->downAttach($path, $name, $message);  
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
                            //$this->downAttach($path, $name, $message);  
                            $attach[] = $name;  
                        }  
                    }
                }  
            }  
        }  
        return $attach;  
    }  
  
    /** 
     * 下载功能 
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
     */  
    public function getAttachData($filePath, $fileName) {  
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