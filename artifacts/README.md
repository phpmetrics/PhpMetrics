# Artifacts

This folder contains tools for generating and publishing artifacts (.phar, .deb...)

Artifacts are stored at Bintray

## Contributing

Each type of artifact MUST be in a specific folder. For example, phar artifact goes in `./phar` folder.

Each folder MUST have a Makefile. This Makefile should be included in `artifacts/Makefile`.

All artifacts MUST be published to Bintray with the travis-ci build. Please check the `./artifacts/bintray.json` file.
