resource "aws_cloudwatch_log_group" "php_log_group" {
  name              = "/php/app/logs"
  retention_in_days = 7
}

resource "aws_iam_role" "cw_agent_role" {
  name = "CloudWatchAgentRole"

  assume_role_policy = jsonencode({
    Version = "2012-10-17",
    Statement = [{
      Action = "sts:AssumeRole",
      Effect = "Allow",
      Principal = {
        Service = "ec2.amazonaws.com"
      }
    }]
  })
}

resource "aws_iam_role_policy_attachment" "cw_attach" {
  role       = aws_iam_role.cw_agent_role.name
  policy_arn = "arn:aws:iam::aws:policy/CloudWatchAgentServerPolicy"
}

resource "aws_instance" "php_server" {
  # Existing EC2 definition...

  iam_instance_profile = aws_iam_instance_profile.cw_profile.name
  user_data = <<-EOF
              #!/bin/bash
              yum install -y amazon-cloudwatch-agent
              /opt/aws/amazon-cloudwatch-agent/bin/amazon-cloudwatch-agent-ctl \
              -a fetch-config \
              -m ec2 \
              -c file:/opt/aws/amazon-cloudwatch-agent/etc/amazon-cloudwatch-agent.json \
              -s
              EOF
}

resource "aws_iam_instance_profile" "cw_profile" {
  name = "cw-instance-profile"
  role = aws_iam_role.cw_agent_role.name
}
