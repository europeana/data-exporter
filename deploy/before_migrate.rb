# Application config
run "ln -nfs #{config.shared_path}/config/config.ini #{config.release_path}/config/config.ini"

# cli-jobs
run "ln -nfs #{config.shared_path}/cli-jobs #{config.release_path}/cli-jobs"

# cli-jobs-completed
run "ln -nfs #{config.shared_path}/cli-jobs-completed #{config.release_path}/cli-jobs-completed"
