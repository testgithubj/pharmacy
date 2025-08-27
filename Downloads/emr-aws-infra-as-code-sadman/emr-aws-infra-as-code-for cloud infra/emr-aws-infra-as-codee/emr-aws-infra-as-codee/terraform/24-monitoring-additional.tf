# # Simplified Additional Terraform configuration for monitoring stack
# # File: 24-monitoring-additional.tf

# # # Data source to get current AWS account ID
# # data "aws_caller_identity" "current" {}

# # Data source to get current AWS region  
# # data "aws_region" "current" {}

# # # Additional IAM policy for EKS nodes to access CloudWatch (optional for enhanced monitoring)
# # resource "aws_iam_role_policy_attachment" "eks_nodes_cloudwatch" {
# #   policy_arn = "arn:aws:iam::aws:policy/CloudWatchAgentServerPolicy"
# #   role       = aws_iam_role.node_group.name
# # }

# # SIMPLIFIED: Minimal IAM role for Grafana Alloy (no CloudWatch Logs permissions)
# resource "aws_iam_role" "alloy" {
#   name = "${local.env}-${local.eks_name}-alloy"

#   assume_role_policy = jsonencode({
#     Version = "2012-10-17"
#     Statement = [
#       {
#         Action = "sts:AssumeRole"
#         Effect = "Allow"
#         Principal = {
#           Service = "pods.eks.amazonaws.com"
#         }
#       }
#     ]
#   })

#   tags = {
#     Name = "${local.env}-${local.eks_name}-alloy"
#   }
# }

# # # OPTIONAL: Only create this policy if you need CloudWatch Logs integration
# # # Since you're using Loki for logs, this is typically not needed
# # resource "aws_iam_policy" "alloy_cloudwatch" {
# #   count       = 0 # Set to 1 if you want CloudWatch Logs integration
# #   name        = "${local.env}-${local.eks_name}-alloy-cloudwatch"
# #   description = "IAM policy for Grafana Alloy CloudWatch Logs (optional)"

# #   policy = jsonencode({
# #     Version = "2012-10-17"
# #     Statement = [
# #       {
# #         Effect = "Allow"
# #         Action = [
# #           "logs:CreateLogGroup",
# #           "logs:CreateLogStream",
# #           "logs:PutLogEvents",
# #           "logs:DescribeLogStreams"
# #         ]
# #         # This ARN breakdown:
# #         # arn:aws:logs:REGION:ACCOUNT_ID:*
# #         # - arn: Standard ARN prefix
# #         # - aws: AWS partition (standard AWS cloud)
# #         # - logs: CloudWatch Logs service
# #         # - REGION: Your AWS region (ap-southeast-1)
# #         # - ACCOUNT_ID: Your AWS account ID (dynamically retrieved)
# #         # - *: All log groups and streams in this account/region
# #         Resource = "arn:aws:logs:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:*"
# #       }
# #     ]
# #   })

# #   tags = {
# #     Name = "${local.env}-${local.eks_name}-alloy-cloudwatch"
# #   }
# # }

# # # OPTIONAL: Attach CloudWatch policy only if created
# # resource "aws_iam_role_policy_attachment" "alloy_cloudwatch" {
# #   count      = length(aws_iam_policy.alloy_cloudwatch)
# #   policy_arn = aws_iam_policy.alloy_cloudwatch[0].arn
# #   role       = aws_iam_role.alloy.name
# # }

# # # Pod Identity Association for Alloy (always needed)
# # resource "aws_eks_pod_identity_association" "alloy" {
# #   cluster_name    = aws_eks_cluster.eks.name
# #   namespace       = "alloy"
# #   service_account = "alloy"
# #   role_arn        = aws_iam_role.alloy.arn

# #   tags = {
# #     Name = "${local.env}-${local.eks_name}-alloy-pod-identity"
# #   }
# # }

# # # IAM role for Prometheus (for AWS service discovery)
# # resource "aws_iam_role" "prometheus" {
# #   name = "${local.env}-${local.eks_name}-prometheus"

# #   assume_role_policy = jsonencode({
# #     Version = "2012-10-17"
# #     Statement = [
# #       {
# #         Action = "sts:AssumeRole"
# #         Effect = "Allow"
# #         Principal = {
# #           Service = "pods.eks.amazonaws.com"
# #         }
# #       }
# #     ]
# #   })

# #   tags = {
# #     Name = "${local.env}-${local.eks_name}-prometheus"
# #   }
# # }

# # # Policy for Prometheus service discovery (EC2 permissions for node discovery)
# # resource "aws_iam_policy" "prometheus" {
# #   name        = "${local.env}-${local.eks_name}-prometheus"
# #   description = "IAM policy for Prometheus service discovery"

# #   policy = jsonencode({
# #     Version = "2012-10-17"
# #     Statement = [
# #       {
# #         Effect = "Allow"
# #         Action = [
# #           "ec2:DescribeInstances",
# #           "ec2:DescribeRegions",
# #           "ec2:DescribeAvailabilityZones",
# #           "ec2:DescribeSecurityGroups",
# #           "ec2:DescribeSubnets",
# #           "ec2:DescribeVpcs"
# #         ]
# #         Resource = "*"
# #       }
# #     ]
# #   })

# #   tags = {
# #     Name = "${local.env}-${local.eks_name}-prometheus"
# #   }
# # }

# # resource "aws_iam_role_policy_attachment" "prometheus" {
# #   policy_arn = aws_iam_policy.prometheus.arn
# #   role       = aws_iam_role.prometheus.name
# # }

# # # Pod Identity Association for Prometheus
# # resource "aws_eks_pod_identity_association" "prometheus" {
# #   cluster_name    = aws_eks_cluster.eks.name
# #   namespace       = "monitoring"
# #   service_account = "prometheus-kube-prometheus-prometheus"
# #   role_arn        = aws_iam_role.prometheus.arn

# #   tags = {
# #     Name = "${local.env}-${local.eks_name}-prometheus-pod-identity"
# #   }
# # }

# # Output the AWS account ID for use in Helm values
# output "aws_account_id" {
#   description = "AWS Account ID for IAM role ARNs"
#   value       = data.aws_caller_identity.current.account_id
# }

# # Output the region for reference
# output "aws_region" {
#   description = "AWS Region"
#   value       = data.aws_region.current.name
# }

# # Output Loki IAM role ARN for Helm values
# output "loki_iam_role_arn_for_helm" {
#   description = "Loki IAM role ARN for use in Helm values"
#   value       = aws_iam_role.loki_s3.arn
# }

# # Output Alloy IAM role ARN for Helm values
# output "alloy_iam_role_arn_for_helm" {
#   description = "Alloy IAM role ARN for use in Helm values"
#   value       = aws_iam_role.alloy.arn
# }

# # # Output Prometheus IAM role ARN for Helm values
# # output "prometheus_iam_role_arn_for_helm" {
# #   description = "Prometheus IAM role ARN for use in Helm values"
# #   value       = aws_iam_role.prometheus.arn
# # }
