<?php
require "../vendor/autoload.php";

use Minishlink\WebPush\WebPush;

class Notification
{
    public static function sendNotification($db, $receiver, $message, $type)
    {
        $db->query('INSERT INTO messages (receiver, title, text, href, notified) VALUES (:receiver, :title, :text, :href, true)',
            array_merge($message, ['receiver' => $receiver]));

        $notificationPreferences = $db->query('SELECT notifyAboutExam, notifyOverEmail, notifyAboutLectureChanges FROM users WHERE username = ?',
            [$receiver])->fetch();

        if ($type == 'exam' && $notificationPreferences['notifyAboutExam'] == 0) return;
        if ($type == 'lectureChange' && $notificationPreferences["notifyAboutLectureChanges"] == 0) return;

        if ($notificationPreferences['notifyOverEmail']) {
            self::sendMail($receiver, $message);
        }

        $pushDataQuery = $db->query('SELECT push FROM devices WHERE push IS NOT NULL AND user = ?', $receiver);
        while ($pushData = $pushDataQuery->fetch()) self::sendPush($pushData["push"], $message);

    }

    private static function sendMail($receiver, $message)
    {
        mail($receiver . '@hft-stuttgart.de', $message['title'],
            '<html><body>' . $message['text'] . '</body></html>',
            "Return-Path: HFT App <info@hft-app.de>\r\n" .
            "Reply-To: HFT App <info@hft-app.de>\r\n" .
            "From: HFT App <info@hft-app.de>\r\n" .
            "Organization: Luniverse\r\n" .
            "Content-Type: text/html; charset=utf-8\r\n" .
            "X-Priority: 3\r\n" .
            "X-Mailer: PHP/" . phpversion() . "\r\n" .
            "MIME-Version: 1.0\r\n"
        );
    }

    private static function sendPush($pushData, $message)
    {
        $pushData = json_decode($pushData);

        $auth = [
            'VAPID' => [
                'subject' => 'mailto:info@hft-app.de',
                'pem' => Config::VAPID_PEM,
            ],
        ];

        try {
            $webPush = new WebPush($auth);
            $webPush->setAutomaticPadding(0);

            $webPush->sendNotification($pushData->endpoint,
                json_encode($message),
                $pushData->keys->p256dh,
                $pushData->keys->auth);

            $webPush->flush();
        } catch (Exception $e) {
            error_log($e);
        }
    }
}

?>
