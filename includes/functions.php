<?php
function checkScriptFile($cronjob) {
  $cronInfo = explode(" /usr/bin/sudo sh ", $cronjob);
  if (count($cronInfo) == 2) {
    $scriptFile = $cronInfo[1];
    if (file_exists($scriptFile)) {
      if (is_executable($scriptFile)) {
        return "";
      } else {
        return "Script file is not executable";
      }
    } else {
      return "Script file does not exist";
    }
  } else {
    return "Invalid cronjob format";
  }
}
?>