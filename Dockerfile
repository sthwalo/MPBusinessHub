# Root Dockerfile - redirects to the correct Dockerfile based on service
FROM busybox
CMD ["echo", "Please use the specific Dockerfile in client/ or server/ directories"]