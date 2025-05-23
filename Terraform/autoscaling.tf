resource "aws_launch_template" "php_launch_template" {
  name_prefix   = "php-template"
  image_id      = "ami-0f9fc25dd2506cf6d"
  instance_type = "t2.micro"

  user_data = base64encode(<<-EOF
              #!/bin/bash
              yum update -y
              yum install -y apache2 php
              systemctl start apache2
              EOF
              )

  vpc_security_group_ids = [aws_security_group.php_sg.id]

  tag_specifications {
    resource_type = "instance"
    tags = {
      Name = "PHPAppAuto"
    }
  }
}

resource "aws_autoscaling_group" "php_asg" {
  desired_capacity     = 1
  max_size             = 3
  min_size             = 1
  vpc_zone_identifier  = ["subnet-xxxxxx"] # Replace with your subnet ID

  launch_template {
    id      = aws_launch_template.php_launch_template.id
    version = "$Latest"
  }

  tag {
    key                 = "Name"
    value               = "PHPAppAuto"
    propagate_at_launch = true
  }
}
