# Map of user locations

## Description

A plugin that uses the free leaflet.js and simplemaps database to show pins for the locations of users on a map.

If a location contains more than one user, it will show as a darker pin, and contain information about all the users located there.

## Installation

Place in /filter and enable in the "Manage filters" administration.

## Configuration

In the filter settings, you can choose what text to display when a pin is clicked.

## Usage

Put **{{ mapofusers }}** in any text where the filter is active to show either the Locations of all users on the site, or the locations of all users in the course (when inside a course).

If you want to show all the users even inside a course, use **{{ mapofusers all }}**.

