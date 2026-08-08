# Homebrew formula for PhpMetrics.
#
# This file is the source of truth, but it is NOT what `brew install` reads:
# Homebrew only looks inside "taps", i.e. repositories named homebrew-<something>.
# .github/workflows/homebrew.yml mirrors this file to PhpMetrics/homebrew-phpmetrics
# every time a release is promoted, which is what users actually install from:
#
#     brew tap phpmetrics/phpmetrics
#     brew install phpmetrics
#
# The url and sha256 lines below are rewritten by that same workflow.
# Do not bump them by hand; everything else can be edited freely.
#
# The formula ships the phar rather than the standalone binaries attached to each
# release. Those are ad-hoc signed (CodeDirectory identifier "micro.sfx", no
# Developer ID and no notarization), so macOS does run them once Homebrew has
# downloaded them, but shipping them would mean four url/sha256 pairs to keep in
# sync and a formula homebrew-core would not accept. The phar gives one formula
# for every architecture and matches what phpstan, php-cs-fixer and psalm do in
# homebrew-core. The cost is `depends_on "php"`, which pulls Homebrew's own PHP
# even for a user who already has one.
class Phpmetrics < Formula
  desc "Static analyzer for PHP: coupling, cyclomatic complexity, maintainability index"
  homepage "https://phpmetrics.github.io/website/"
  # Homebrew reads the version off the tag in the url, dropping the "v" prefix.
  url "https://github.com/PhpMetrics/PhpMetrics/releases/download/v2.9.0/phpmetrics.phar"
  sha256 "52c1f14aa0b94695eb34cd1d0049ce7f6a7f89d7d3c3d46bfa45526d3412698a"
  license "MIT"

  livecheck do
    url :stable
    strategy :github_latest
  end

  depends_on "php"

  def install
    libexec.install "phpmetrics.phar"

    # A wrapper rather than a symlink: it pins the php Homebrew depends on,
    # instead of whichever php happens to come first in the user's PATH.
    (bin/"phpmetrics").write <<~SHELL
      #!/bin/bash
      exec "#{formula_opt_bin("php")}/php" "#{libexec}/phpmetrics.phar" "$@"
    SHELL
  end

  test do
    # --version prints "PhpMetrics <version> <http://www.phpmetrics.org>"
    assert_match version.to_s, shell_output("#{bin}/phpmetrics --version")

    (testpath/"src/Foo.php").write <<~PHP
      <?php
      class Foo
      {
          public function bar($a)
          {
              return $a > 0 ? $a : -$a;
          }
      }
    PHP

    system bin/"phpmetrics", "--report-json=#{testpath}/report.json", "#{testpath}/src"
    assert_path_exists testpath/"report.json"
  end
end
