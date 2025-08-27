# # # Grafana Alloy Helm Deployment for Log Collection
# # # Based on https://grafana.com/docs/alloy/latest/configure/kubernetes/

# # Create namespace for monitoring tools
# resource "kubernetes_namespace" "monitoring" {
#   metadata {
#     name = "monitoring"
#     labels = {
#       name        = "monitoring"
#       environment = local.env
#       managed-by  = "terraform"
#     }
#   }
# }

# # # Note: Authentication is handled via templatefile substitution in alloy.yaml
# # # No separate secret needed since credentials are embedded directly in config

# # # Deploy Alloy using Helm with proper Loki integration
# # resource "helm_release" "alloy" {
# #   name = "alloy"

# #   repository       = "https://grafana.github.io/helm-charts"
# #   chart            = "alloy"
# #   namespace        = kubernetes_namespace.monitoring.metadata[0].name
# #   create_namespace = false
# #   version          = "1.2.1" # Latest stable version

# #   # Use templated values file with authentication
# #   values = [
# #     templatefile("${path.module}/values/alloy.yaml", {
# #       LOKI_USERNAME = var.loki_username
# #       LOKI_PASSWORD = var.loki_password
# #       CLUSTER_NAME  = "${local.env}-${local.eks_name}"
# #       REGION        = local.region
# #       ENVIRONMENT   = local.env
# #       # HOSTNAME      = "${HOSTNAME}" # Double $ escapes it for template
# #     })
# #   ]

# #   # Set cluster identification via environment variables
# #   set {
# #     name  = "alloy.extraEnv[0].name"
# #     value = "CLUSTER_NAME"
# #   }

# #   set {
# #     name  = "alloy.extraEnv[0].value"
# #     value = "${local.env}-${local.eks_name}"
# #   }

# #   set {
# #     name  = "alloy.extraEnv[1].name"
# #     value = "AWS_REGION"
# #   }

# #   set {
# #     name  = "alloy.extraEnv[1].value"
# #     value = local.region
# #   }

# #   set {
# #     name  = "alloy.extraEnv[2].name"
# #     value = "ENVIRONMENT"
# #   }

# #   set {
# #     name  = "alloy.extraEnv[2].value"
# #     value = local.env
# #   }

# #   depends_on = [
# #     aws_eks_node_group.general,
# #     kubernetes_namespace.monitoring,
# #     helm_release.loki,            # Ensure Loki is deployed first
# #     helm_release.prometheus_stack # Ensure Prometheus stack is ready
# #   ]

# #   # Wait for all resources to be ready
# #   wait         = true
# #   timeout      = 600
# #   force_update = true
# #   reset_values = true
# # }

# # # ServiceMonitor for Prometheus integration (enabled with Prometheus Operator)
# # resource "kubernetes_manifest" "alloy_service_monitor" {
# #   count = 1 # Enabled since we're installing Prometheus Operator

# #   manifest = {
# #     apiVersion = "monitoring.coreos.com/v1"
# #     kind       = "ServiceMonitor"
# #     metadata = {
# #       name      = "alloy"
# #       namespace = kubernetes_namespace.monitoring.metadata[0].name
# #       labels = {
# #         app       = "alloy"
# #         component = "monitoring"
# #       }
# #     }
# #     spec = {
# #       selector = {
# #         matchLabels = {
# #           "app.kubernetes.io/name"     = "alloy"
# #           "app.kubernetes.io/instance" = "alloy"
# #         }
# #       }
# #       endpoints = [{
# #         port          = "http-metrics"
# #         path          = "/metrics"
# #         interval      = "30s"
# #         scrapeTimeout = "10s"
# #       }]
# #     }
# #   }

# #   depends_on = [helm_release.alloy, helm_release.prometheus_stack]
# # }

# # # Note: Outputs are centralized in 26-outputs.tf to avoid duplication
