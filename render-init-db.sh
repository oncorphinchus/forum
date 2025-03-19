#!/bin/bash

# This script initializes the database for deployment on Render

# Combine all SQL files into a single initialization file
echo "-- Combined initialization script for Render deployment" > render-init.sql
cat database.sql >> render-init.sql
echo "" >> render-init.sql
cat topic_views.sql >> render-init.sql
echo "" >> render-init.sql
cat contact_messages.sql >> render-init.sql

echo "Database initialization script created as render-init.sql"
echo "You'll need to run this script manually after the database is created on Render." 