<?php


class LaunchRequest
{

    /**
     * When user opens skill without an intent
     */
    public function processLaunchRequest($userId, $hasAPLSupport): array {
        $builder = new ResponseBuilder();
        $database = new Database();

        $stmt = $database->getMysql()->prepare("SELECT * FROM istmeinzugpuenktlich WHERE userId = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if($row["stationId"] != null){
            if($row["watchedTrains"] != null){
                $listTrainChanges = new ListTrainUpdates($row["stationId"], $userId);

                $message = $listTrainChanges->requestChanges();

                $delayList = $listTrainChanges->getDelayList();
                $uiItems = [];
                foreach ($delayList as $item){
                    array_push($uiItems, [
                        "primaryText" => $item["plannedDeparture"],
                        "secondaryText" => "Richtung " . $item['train']->getDestination() . " auf Gleis " . $item['train']->getPlatform(),
                        "tertiaryText" => ($item["delay"] != 0) ? "+ " . $item["delay"] . " Min" : null,
                        "imageAlignment" => "center",
                        "imageBlurredBackground" => true,
                        "imageScale" => "best-fit"
                    ]);
                }

                if(count($delayList) != 0){
                    if($hasAPLSupport()){
                        $builder->speechAPL($message, $uiItems);
                    } else {
                        $builder->speechTextWithCard($message);
                    }
                } else {
                    if ($row['hasRemindersHint'] == 0) {
                        $remindersStatement = $database->getMysql()->prepare('UPDATE istmeinzugpuenktlich SET hasRemindersHint = 1 WHERE userId = ?');
                        $remindersStatement->execute([$userId]);

                        $builder->speechTextAndReprompt(
                            $message . ". Soll ich dich in Zukunft erinnern nochmal zu fragen?",
                            "Soll ich dich von nun an erinnern die Verpätungen abzufragen?",
                            ["intent" => "recurrenceQuestion"]
                        );
                    } else if ($row['hasRoutinesHint'] == 0) {
                        $remindersStatement = $database->getMysql()->prepare('UPDATE istmeinzugpuenktlich SET hasRoutinesHint = 1 WHERE userId = ?');
                        $remindersStatement->execute([$userId]);

                        $builder->speechTextAndReprompt(
                            $message . ". Wusstest du das du diesen Skill auch mithilfe von Routinen nutzen kannst. Soll ich dir zeigen wie?",
                            "Möchtest du wissen wie du diesen Skill mit Routinen nutzen kannst?",
                            ["intent" => "routinesHelp"]
                        );
                    } else {
                        $builder->speechText(
                            $message,
                        );
                    }
                }
            } else {
                $builder->speechTextAndReprompt("Du hast noch keinen Zug auf deiner Liste. Möchtest du jetzt einen hinzufügen?",
                    "Möchtest du einen Zug zu deiner Liste hinzufügen?",
                    ["intent" => "addTrain"]);
            }
        } else {
            $builder->speechTextAndReprompt("Ich habe deinen Heimatbahnhof noch nicht gespeichert. Möchtest du mir verraten was dein Heimatbahnhof ist?",
                "Was ist dein Heimatbahnhof?",
                []
            );
        }

        return $builder->getResponse();
    }

}