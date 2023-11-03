# Webshell

My PHP webshell comprised of lots of cool ideas from other shells.
Contains more features than a basic shell, but nothing I don't use during actual testing. Slightly larger file, hopefully useful features! 

## Table of Contents

- [Installation](#installation)
- [Features](#features)
- [Usage](#usage)
- [Example](#example)
- [Important](#important)

## Installation

Just clone the repo!

```bash
$ git clone https://github.com/duck-sec/webshell
```

## Features

Features include: 
- Cookie based authentication
- Masquerade as an Apache 404 for unauthorised users
- Execute commands
- Upload / Download files
- Spawn a reverse shell (catch with netcat)
- Basic file table to make navigation easy

## Usage

Before "Installing" the shell on your target server, set a password at the top of the script:

```
#Set password value, which must be set in the cookie

$password = "password";
```

Ideally don't use "password"...
Pass your chosen password to the shell in a cookie named "auth" to gain access. 
Attempting to access the shell without this cookie will cause the shell to pretend to be an Apache not found page.

![Screenshot](noauth.png)


## Example

![Screenshot](screenshot.png)

## Important

This code is provided for educational purposes as well as for use in legitimate, AUTHORISED, security testing.
Do not use this shell to attempt to access any system which you do not have explicit permission to test or practice on.