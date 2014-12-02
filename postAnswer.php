<?php



if (isset($_POST['gameId'], $_POST['teamId'], $_POST['questionId'], $_POST['answer'], $_POST['timestamp'])) {
    $gameID = $_POST['gameId'];
    $teamId = $_POST['teamId'];
    $question = $_POST['questionId'];
    $answer = $_POST['answer'];
    $timestamp = $_POST['timestamp'];
    
$Mysql = new Mysql_spil();
            $games = $Mysql->get_games();

            for ($i = 0; $i < sizeof($games); $i++) {
                if ($games[$i][0] === $gameId) {
                    $gameName = $games[$i][1];
                }
            }

            $query = 'INSERT INTO Game_' . $gameName . '.Team_pos_' . $gameId . ' VALUES (?, ?, ?)';

            if ($stmt = $this->conn->prepare($query)) {
                $stmt->bind_param('iii', $question, $answer, $time);
                $stmt->execute();
                $stmt->close();
            }    
}
