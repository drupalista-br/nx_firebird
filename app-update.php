<?php

$commit_last = '';
$commit_current = file_get_contents("app-update.commit");

if (file_exists("tmp/commit.txt")) {
  $commit_last = file_get_contents("tmp/commit.txt");
}

if ($commit_last != $commit_current) {
  passthru("git pull origin master &&
		   git checkout $commit_current");
}
