# Europeana Data Exporter
Export datasets into xml files that can be used by [GWToolset][1].


## Setup
1. Copy the application.config.sample.php file to application.config.php and alter its contents as appropriate.
1. In the application root directory, create the following directories:
   1. cli-jobs
   1. cli-jobs-completed
   1. make sure the web server user has read/write priviledges to these directories.
1. Create crontabs to run the following commands:
   1. php cli-scripts/process-jobs.cli.php
   1. php cli-scripts/process-control-job.cli.php
   1. php cli-scripts/process-completed-job-groups.cli.php

[1]:https://www.mediawiki.org/wiki/Extension:GWToolset
