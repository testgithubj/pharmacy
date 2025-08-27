# # Prometheus Stack (kube-prometheus-stack) Deployment
# # This includes Prometheus, Grafana, AlertManager, and Prometheus Operator

# # Deploy kube-prometheus-stack using Helm
# resource "helm_release" "prometheus_stack" {
#   name = "prometheus"

#   repository       = "https://prometheus-community.github.io/helm-charts"
#   chart            = "kube-prometheus-stack"
#   namespace        = kubernetes_namespace.monitoring.metadata[0].name
#   create_namespace = false
#   version          = "76.4.0" # Latest stable version

#   # Use custom values file for Prometheus stack
#   values = [
#     templatefile("${path.module}/values/prometheus-stack.yaml", {
#       CLUSTER_NAME  = "${local.env}-${local.eks_name}"
#       ENVIRONMENT   = local.env
#       REGION        = local.region
#       DOMAIN_NAME   = local.domain_name
#       LOKI_USERNAME = var.loki_username
#       LOKI_PASSWORD = var.loki_password
#     })
#   ]

#   # Override specific values via set blocks for better control
#   set {
#     name  = "grafana.adminPassword"
#     value = var.loki_password # Reuse Loki password for simplicity
#   }

#   set {
#     name  = "grafana.ingress.enabled"
#     value = "true"
#   }

#   set {
#     name  = "grafana.ingress.ingressClassName"
#     value = "external-nginx"
#   }

#   set {
#     name  = "grafana.ingress.hosts[0]"
#     value = "grafana.${replace(local.domain_name, "loki.", "")}" # grafana.grabinsight.com
#   }

#   set {
#     name  = "prometheus.ingress.enabled"
#     value = "true"
#   }

#   set {
#     name  = "prometheus.ingress.ingressClassName"
#     value = "external-nginx"
#   }

#   set {
#     name  = "prometheus.ingress.hosts[0]"
#     value = "prometheus.${replace(local.domain_name, "loki.", "")}" # prometheus.grabinsight.com
#   }

#   depends_on = [
#     aws_eks_node_group.general,
#     kubernetes_namespace.monitoring,
#     helm_release.external_nginx, # Ensure ingress controller exists
#     helm_release.loki            # Ensure Loki is available for data source
#   ]

#   # Wait for all resources to be ready
#   wait         = true
#   timeout      = 900 # 15 minutes for full stack deployment
#   force_update = true
#   reset_values = true
# }

# # Create a secret for Grafana to access Loki
# resource "kubernetes_secret" "grafana_loki_datasource" {
#   metadata {
#     name      = "grafana-loki-datasource"
#     namespace = kubernetes_namespace.monitoring.metadata[0].name
#     labels = {
#       app        = "grafana"
#       component  = "datasource"
#       managed-by = "terraform"
#     }
#   }

#   type = "Opaque"

#   data = {
#     username = var.loki_username
#     password = var.loki_password
#     url      = "https://${local.domain_name}"
#   }

#   depends_on = [kubernetes_namespace.monitoring]
# }

# # ConfigMap for Grafana Loki datasource
# resource "kubernetes_config_map" "grafana_loki_datasource" {
#   metadata {
#     name      = "grafana-loki-datasource"
#     namespace = kubernetes_namespace.monitoring.metadata[0].name
#     labels = {
#       grafana_datasource = "1" # This label makes Grafana auto-discover the datasource
#     }
#   }

#   data = {
#     "loki-datasource.yaml" = yamlencode({
#       apiVersion = 1
#       datasources = [{
#         name          = "Loki"
#         type          = "loki"
#         access        = "proxy"
#         url           = "https://${local.domain_name}"
#         basicAuth     = true
#         basicAuthUser = var.loki_username
#         secureJsonData = {
#           basicAuthPassword = var.loki_password
#         }
#         jsonData = {
#           maxLines = 1000
#           derivedFields = [{
#             name          = "TraceID"
#             matcherRegex  = "trace_id=(\\w+)"
#             url           = "$${__value.raw}"
#             datasourceUid = "jaeger"
#           }]
#         }
#         isDefault = false
#       }]
#     })
#   }

#   depends_on = [helm_release.prometheus_stack]
# }

# # Optional: Create ingress for AlertManager
# resource "kubernetes_ingress_v1" "alertmanager_ingress" {
#   metadata {
#     name      = "alertmanager-ingress"
#     namespace = kubernetes_namespace.monitoring.metadata[0].name
#     annotations = {
#       "kubernetes.io/ingress.class"                = "external-nginx"
#       "nginx.ingress.kubernetes.io/rewrite-target" = "/"
#       #   "cert-manager.io/cluster-issuer"             = "letsencrypt-prod"
#     }
#   }

#   spec {
#     ingress_class_name = "external-nginx"

#     tls {
#       hosts       = ["alertmanager.${replace(local.domain_name, "loki.", "")}"]
#       secret_name = "alertmanager-tls"
#     }

#     rule {
#       host = "alertmanager.${replace(local.domain_name, "loki.", "")}"
#       http {
#         path {
#           path      = "/"
#           path_type = "Prefix"
#           backend {
#             service {
#               name = "prometheus-kube-prometheus-alertmanager"
#               port {
#                 number = 9093
#               }
#             }
#           }
#         }
#       }
#     }
#   }

#   depends_on = [helm_release.prometheus_stack]
# }
# # Create basic auth secret for Prometheus ingress
# resource "kubernetes_secret" "prometheus_basic_auth" {
#   metadata {
#     name      = "prometheus-basic-auth"
#     namespace = kubernetes_namespace.monitoring.metadata[0].name
#   }

#   type = "Opaque"

#   data = {
#     auth = "${var.loki_username}:${bcrypt(var.loki_password)}"
#   }

#   depends_on = [kubernetes_namespace.monitoring]
# }

# # Create basic auth secret for AlertManager ingress
# resource "kubernetes_secret" "alertmanager_basic_auth" {
#   metadata {
#     name      = "alertmanager-basic-auth"
#     namespace = kubernetes_namespace.monitoring.metadata[0].name
#   }

#   type = "Opaque"

#   data = {
#     auth = "${var.loki_username}:${bcrypt(var.loki_password)}"
#   }

#   depends_on = [kubernetes_namespace.monitoring]
# }
