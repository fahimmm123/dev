provider "aws" {
  region = "us-north-1"
}

resource "aws_instance" "php_server" {
  ami           = "ami-00f34bf9aeacdf007"
  instance_type = "t2.micro"

  tags = {
    Name = "PHPApp"
  }
}
