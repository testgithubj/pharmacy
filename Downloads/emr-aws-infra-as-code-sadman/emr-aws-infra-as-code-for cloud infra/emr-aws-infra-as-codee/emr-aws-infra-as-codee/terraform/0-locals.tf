locals {
  env         = "development"
  region      = "ap-southeast-1"
  zone1       = "ap-southeast-1a"
  zone2       = "ap-southeast-1b"
  eks_name    = "demo"
  eks_version = "1.33"
  domain_name = "loki.grabinsight.com"
}


# Source: local.domain_name = "loki.grabinsight.com"
#                                     ↓
# Generated Domains:
# ├─ Loki:         loki.grabinsight.com        (original)
# ├─ Grafana:      grafana.grabinsight.com    (loki → grafana) 
# ├─ Prometheus:   prometheus.grabinsight.com (loki → prometheus)
# └─ AlertManager: alertmanager.grabinsight.com (loki → alertmanager)
