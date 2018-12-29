workflow "Lint" {
  on = "push"
  resolves = ["Test on Travis CI"]
}

action "PHP-CS-Fixer" {
  uses = "docker://oskarstark/php-cs-fixer-ga"
  secrets = ["GITHUB_TOKEN"]
  args = "--diff --dry-run"
}

action "Test on Travis CI" {
  uses = "travis-ci/actions@master"
  needs = ["PHP-CS-Fixer"]
  secrets = ["TRAVIS_TOKEN"]
}
