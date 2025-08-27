# # S3 Buckets for Loki Storage
# resource "aws_s3_bucket" "loki_chunks" {
#   bucket = "${local.env}-${local.eks_name}-loki-chunks"

#   tags = {
#     Name = "${local.env}-${local.eks_name}-loki-chunks"
#   }
# }

# resource "aws_s3_bucket" "loki_ruler" {
#   bucket = "${local.env}-${local.eks_name}-loki-ruler"

#   tags = {
#     Name = "${local.env}-${local.eks_name}-loki-ruler"
#   }
# }

# resource "aws_s3_bucket" "loki_admin" {
#   bucket = "${local.env}-${local.eks_name}-loki-admin"

#   tags = {
#     Name = "${local.env}-${local.eks_name}-loki-admin"
#   }
# }

# # Enable versioning for all buckets
# resource "aws_s3_bucket_versioning" "loki_chunks" {
#   bucket = aws_s3_bucket.loki_chunks.id
#   versioning_configuration {
#     status = "Enabled"
#   }
# }

# resource "aws_s3_bucket_versioning" "loki_ruler" {
#   bucket = aws_s3_bucket.loki_ruler.id
#   versioning_configuration {
#     status = "Enabled"
#   }
# }

# resource "aws_s3_bucket_versioning" "loki_admin" {
#   bucket = aws_s3_bucket.loki_admin.id
#   versioning_configuration {
#     status = "Enabled"
#   }
# }

# # Enable server-side encryption
# resource "aws_s3_bucket_server_side_encryption_configuration" "loki_chunks" {
#   bucket = aws_s3_bucket.loki_chunks.id

#   rule {
#     apply_server_side_encryption_by_default {
#       sse_algorithm = "AES256"
#     }
#   }
# }

# resource "aws_s3_bucket_server_side_encryption_configuration" "loki_ruler" {
#   bucket = aws_s3_bucket.loki_ruler.id

#   rule {
#     apply_server_side_encryption_by_default {
#       sse_algorithm = "AES256"
#     }
#   }
# }

# resource "aws_s3_bucket_server_side_encryption_configuration" "loki_admin" {
#   bucket = aws_s3_bucket.loki_admin.id

#   rule {
#     apply_server_side_encryption_by_default {
#       sse_algorithm = "AES256"
#     }
#   }
# }

# # Block public access
# resource "aws_s3_bucket_public_access_block" "loki_chunks" {
#   bucket = aws_s3_bucket.loki_chunks.id

#   block_public_acls       = true
#   block_public_policy     = true
#   ignore_public_acls      = true
#   restrict_public_buckets = true
# }

# resource "aws_s3_bucket_public_access_block" "loki_ruler" {
#   bucket = aws_s3_bucket.loki_ruler.id

#   block_public_acls       = true
#   block_public_policy     = true
#   ignore_public_acls      = true
#   restrict_public_buckets = true
# }

# resource "aws_s3_bucket_public_access_block" "loki_admin" {
#   bucket = aws_s3_bucket.loki_admin.id

#   block_public_acls       = true
#   block_public_policy     = true
#   ignore_public_acls      = true
#   restrict_public_buckets = true
# }

# # Lifecycle policies for cost optimization
# resource "aws_s3_bucket_lifecycle_configuration" "loki_chunks" {
#   bucket = aws_s3_bucket.loki_chunks.id

#   rule {
#     id     = "transition_to_ia"
#     status = "Enabled"

#     transition {
#       days          = 30
#       storage_class = "STANDARD_IA"
#     }

#     transition {
#       days          = 90
#       storage_class = "GLACIER"
#     }

#     expiration {
#       days = 365
#     }
#   }
# }

# resource "aws_s3_bucket_lifecycle_configuration" "loki_ruler" {
#   bucket = aws_s3_bucket.loki_ruler.id

#   rule {
#     id     = "transition_to_ia"
#     status = "Enabled"

#     transition {
#       days          = 30
#       storage_class = "STANDARD_IA"
#     }

#     transition {
#       days          = 90
#       storage_class = "GLACIER"
#     }

#     expiration {
#       days = 365
#     }
#   }
# }

# resource "aws_s3_bucket_lifecycle_configuration" "loki_admin" {
#   bucket = aws_s3_bucket.loki_admin.id

#   rule {
#     id     = "transition_to_ia"
#     status = "Enabled"

#     transition {
#       days          = 30
#       storage_class = "STANDARD_IA"
#     }

#     transition {
#       days          = 90
#       storage_class = "GLACIER"
#     }

#     expiration {
#       days = 365
#     }
#   }
# }
