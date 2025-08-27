# # Variables for Loki authentication
# variable "loki_username" {
#   description = "Username for Loki basic authentication"
#   type        = string
#   default     = "loki"
# }

# variable "loki_password" {
#   description = "Password for Loki basic authentication"
#   type        = string
#   sensitive   = true
# }

# # Create Kubernetes secrets for Loki authentication
# resource "kubernetes_secret" "loki_basic_auth" {
#   metadata {
#     name      = "loki-basic-auth"
#     namespace = "loki"
#   }

#   type = "Opaque"

#   data = {
#     ".htpasswd" = "${var.loki_username}:${bcrypt(var.loki_password)}"
#   }

#   depends_on = [kubernetes_namespace.loki]
# }

# resource "kubernetes_secret" "canary_basic_auth" {
#   metadata {
#     name      = "canary-basic-auth"
#     namespace = "loki"
#   }

#   type = "Opaque"

#   data = {
#     username = var.loki_username
#     password = var.loki_password
#   }

#   depends_on = [kubernetes_namespace.loki]
# }

# # Create the Loki namespace
# resource "kubernetes_namespace" "loki" {
#   metadata {
#     name = "loki"
#   }
# }

# # Loki Deployment using Helm
# resource "helm_release" "loki" {
#   name = "loki"

#   repository       = "https://grafana.github.io/helm-charts"
#   chart            = "loki"
#   namespace        = "loki"
#   create_namespace = false
#   version          = "6.16.0" # Use a stable version

#   # Use the values file with template substitution
#   values = [
#     templatefile("${path.module}/values/loki.yaml", {
#       AWS_REGION        = local.region
#       CHUNK_BUCKET_NAME = aws_s3_bucket.loki_chunks.bucket
#       RULER_BUCKET_NAME = aws_s3_bucket.loki_ruler.bucket
#       ADMIN_BUCKET_NAME = aws_s3_bucket.loki_admin.bucket
#       IAM_ROLE_ARN      = aws_iam_role.loki_s3.arn
#       DOMAIN_NAME       = local.domain_name
#     })
#   ]

#   depends_on = [
#     aws_eks_node_group.general,
#     aws_eks_pod_identity_association.loki,
#     kubernetes_secret.loki_basic_auth,
#     kubernetes_secret.canary_basic_auth,
#     kubernetes_namespace.loki,
#     helm_release.external_nginx # Ensure ingress controller is deployed first
#   ]

#   # Wait for all resources to be ready
#   wait    = true
#   timeout = 900 # 15 minutes
# }

# # # Data source to get current AWS region
# data "aws_region" "current" {}

# # Outputs
# output "loki_ingress_url" {
#   description = "Loki ingress URL"
#   value       = "https://${local.domain_name}"
# }

# output "loki_username" {
#   description = "Loki authentication username"
#   value       = var.loki_username
# }

# output "loki_test_command" {
#   description = "Command to test Loki authentication"
#   value       = "curl -u '${var.loki_username}:${var.loki_password}' https://${local.domain_name}/ready"
#   sensitive   = true
# }
