<?php namespace Collection;
class LectureChanges extends \Collection {

    public function fetch($gateway) {
        $this->list = [];

        // Parse courses
        for ($relativeDay = 0; $relativeDay < 7; $relativeDay++) {
            $day = time() + 86400 * $relativeDay;

            $view = $gateway->fetch($gateway::host . '?state=currentLectures&type=1&next=CurrentLectures.vm&nextdir=ressourcenManager&P.Print=&HISCalendar_Date=' . date('d.m.Y', $day));
            foreach ($view->query('//body/table/tr[position() > 1]') as $row){
                $link = $view->query('td[4]/a', $row)->item(0);
                parse_str(parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
                $this->list[] = [
                    'day' => $day,
                    'startTime' => trim($view->query('td[1]', $row)->item(0)->nodeValue),
                    'endTime' => trim($view->query('td[2]', $row)->item(0)->nodeValue),
                    'title' => trim($link->nodeValue),
                    'room' => trim($view->query('td[6]', $row)->item(0)->nodeValue),
                    'professor' => trim($view->query('td[8]', $row)->item(0)->nodeValue),
                    'comment' => trim($view->query('td[9]', $row)->item(0)->nodeValue),
                    'course' => $query['publishid'],
			    ];
            }
        }
    }

    public function write($db){
        $newChanges = [];
        foreach ($this->list as $lectureChange) {
            try {
                $db->query('INSERT INTO lecture_changes (day, start, end, title, room, professor, comment, course) 
			                  VALUES (:day, :startTime, :endTime, :title, :room, :professor, :comment, :course)',
                    array_merge($lectureChange, [
                        'day' => date('Y-m-d', $lectureChange['day']),
                        'startTime' => $lectureChange['startTime'] . ':00',
                        'endTime' => $lectureChange['endTime'] . ':00',
                    ]));
                $newChanges[] = $lectureChange;
            } catch (\Exception $e) {}
        }

        return $newChanges;
    }
}
?>
