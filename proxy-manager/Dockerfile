FROM golang:1.19-alpine AS builder

WORKDIR /build

# Install dependencies
RUN apk add --no-cache git

# Copy go mod and sum files
COPY go.mod ./

# Download all dependencies
RUN go mod download

# Copy the source code
COPY . .

# Build the application with static linking
RUN CGO_ENABLED=0 GOOS=linux go build -a -installsuffix cgo -o proxy-manager .

# Use a smaller base image for the final stage
FROM alpine:3.16

WORKDIR /app

# Copy the binary from the builder stage
COPY --from=builder /build/proxy-manager .
# Copy the proxies.txt file
COPY proxies.txt .
# Copy the .env file
COPY .env .

# Expose port 8080
EXPOSE 8080

# Command to run the executable
CMD ["/app/proxy-manager"]
