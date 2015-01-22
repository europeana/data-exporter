# Application config
run "ln -nfs #{config.shared_path}/config/application.config.php #{config.release_path}/config/application.config.php"

# cli-jobs
run "mkdir -p #{config.shared_path}/cli-jobs"
run "ln -nfs #{config.shared_path}/cli-jobs #{config.release_path}/cli-jobs"

# cli-jobs-completed
run "mkdir -p #{config.shared_path}/cli-jobs-completed"
run "ln -nfs #{config.shared_path}/cli-jobs-completed #{config.release_path}/cli-jobs-completed"
