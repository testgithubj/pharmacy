# # IAM Role for Loki S3 Access using EKS Pod Identity
# data "aws_iam_policy_document" "loki_s3_assume_role" {
#   statement {
#     effect = "Allow"

#     principals {
#       type        = "Service"
#       identifiers = ["pods.eks.amazonaws.com"]
#     }

#     actions = [
#       "sts:AssumeRole",
#       "sts:TagSession"
#     ]
#   }
# }

# resource "aws_iam_role" "loki_s3" {
#   name               = "${aws_eks_cluster.eks.name}-loki-s3"
#   assume_role_policy = data.aws_iam_policy_document.loki_s3_assume_role.json

#   tags = {
#     Name = "${aws_eks_cluster.eks.name}-loki-s3"
#   }
# }

# # IAM Policy for Loki S3 Access with explicit bucket permissions
# data "aws_iam_policy_document" "loki_s3_policy" {
#   statement {
#     sid    = "LokiChunksBucketAccess"
#     effect = "Allow"
#     actions = [
#       "s3:ListBucket",
#       "s3:GetBucketLocation"
#     ]
#     resources = [
#       aws_s3_bucket.loki_chunks.arn,
#       aws_s3_bucket.loki_ruler.arn,
#       aws_s3_bucket.loki_admin.arn
#     ]
#   }

#   statement {
#     sid    = "LokiObjectAccess"
#     effect = "Allow"
#     actions = [
#       "s3:GetObject",
#       "s3:PutObject",
#       "s3:DeleteObject",
#       "s3:GetObjectVersion",
#       "s3:DeleteObjectVersion"
#     ]
#     resources = [
#       "${aws_s3_bucket.loki_chunks.arn}/*",
#       "${aws_s3_bucket.loki_ruler.arn}/*",
#       "${aws_s3_bucket.loki_admin.arn}/*"
#     ]
#   }
# }

# resource "aws_iam_policy" "loki_s3" {
#   name        = "${aws_eks_cluster.eks.name}-loki-s3"
#   description = "IAM policy for Loki to access S3 buckets"
#   policy      = data.aws_iam_policy_document.loki_s3_policy.json

#   tags = {
#     Name = "${aws_eks_cluster.eks.name}-loki-s3"
#   }
# }

# resource "aws_iam_role_policy_attachment" "loki_s3" {
#   policy_arn = aws_iam_policy.loki_s3.arn
#   role       = aws_iam_role.loki_s3.name
# }

# # Pod Identity Association for Loki
# resource "aws_eks_pod_identity_association" "loki" {
#   cluster_name    = aws_eks_cluster.eks.name
#   namespace       = "loki"
#   service_account = "loki"
#   role_arn        = aws_iam_role.loki_s3.arn

#   tags = {
#     Name = "${aws_eks_cluster.eks.name}-loki-pod-identity"
#   }
# }

# # Output the IAM role ARN for reference
# output "loki_iam_role_arn" {
#   description = "IAM role ARN for Loki"
#   value       = aws_iam_role.loki_s3.arn
# }
