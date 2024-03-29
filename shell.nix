# Pinned to commit where we get the latest version of symfony-cli
# Otherwise we should pin to stable version
{ pkgs ? import (fetchTarball "https://github.com/NixOS/nixpkgs/archive/a2d1be594789d2c2d62bea6503edbee8648a1b19.tar.gz") {} }:

pkgs.mkShell {
  buildInputs = [
    pkgs.php74
    pkgs.php74Packages.psysh
    pkgs.php74Packages.composer
    pkgs.symfony-cli
    pkgs.php74Packages.psysh
  ];
}

