<?php namespace Collection;
class LectureChanges extends \Collection {

    public function fetch($gateway) {
        $this->list = [];

        // Parse courses
        for ($relativeDay = 0; $relativeDay < 7; $relativeDay++) {
            $date = time() + 86400 * $relativeDay;

            $view = $gateway->fetch($gateway::host . '?state=currentLectures&type=1&next=CurrentLectures.vm&nextdir=ressourcenManager&navigationPosition=lectures%2CcanceledLectures&breadcrumb=canceledLectures&topitem=lectures&subitem=canceledLectures&HISCalendar_Date=' . date("d.m.Y", $date));
            foreach ($view->query('/div[@class="divcontent"]/table/tr[position() > 1]/') as $row){
                $link = $view->query("[td[3]]", $row)->item(0)->nodeValue;
                parse_str(parse_url($link->href, PHP_URL_QUERY), $query);
                $this->list[] = [
                    'date' => $date,
                    'startTime' => trim($view->query("[td[1]]", $row)->item(0)->nodeValue),
                    'endTime' => trim($view->query("[td[2]]", $row)->item(0)->nodeValue),
                    'courseTitle' => trim($view->query("[td[3]/a]", $row)->item(0)->nodeValue),
                    'room' => trim($view->query("[td[5]]", $row)->item(0)->nodeValue),
                    'professor' => trim($view->query("[td[7]]", $row)->item(0)->nodeValue),
                    'comment' => trim($view->query("[td[8]]", $row)->item(0)->nodeValue),
                    'course' => $query["amp;publishid"],
			    ];
            }
        }
    }

    public function write($db)
    {
        $newChanges = [];
        foreach($this->list as $lectureChange) {
            $db->query('INSERT INTO lectureChanges (date, startTime, endTime, courseTitle, room, professor, comment, course) 
			VALUES (:date, :startTime, :endTime, :courseTitle, :room, :professor, :comment, :course)', $lectureChange);

            if($db.errorCode() == 0) $newChanges[] = $lectureChange;
        }

        return $newChanges;
    }
}
?>
