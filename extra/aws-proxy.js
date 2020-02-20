/**
 * @file AWS Lambda function to pull data from the endpoint, anonymize/filter it
 * and report it via the AWS API Gateway to the blog frontend in the same format
 * so it effectively becomes a drop-in replacement for a connection to the real
 * endpoint.
 */
/* eslint strict: ["error", "global"], yoda: 0 */
/* global require, exports, process */

'use strict';

const https = require('https');

/*
 * Pass the data to send as `event.data`, and the request options as
 * `event.options`. For more information see the HTTPS module documentation
 * at https://nodejs.org/api/https.html.
 *
 * Will succeed with the response body.
 */
exports.handler = (event, context, callback) => {

  // Figure out what API path we're using:
  var path = event.path || process.env.LOCATION_API_ENDPOINT;
  
  // Flag whether or not to verify we're on a trip
  let verifyTrip = false;

  if (path === '/api/location/latest') {
    // This is okay: it's a request for the latest update
    verifyTrip = true;
  }
  else if (path.match(/\/api\/location\/history\/timestamp\/\d+$/)) {
    // This is okay, it's a request for a location update at a given time
    verifyTrip = true;
  }
  else if (path.match(/\/api\/trips(\/\d+)?$/)) {
    // This is okay, it's a request for trip details
  }
  else {
    // Not a whitelisted request type, block it.
    var errorResponse = {
      statusCode: 403,
      body: JSON.stringify({error: 'Attempt to use unauthorized API endpoint'})
    };
    callback(null, errorResponse);
    return;
  }


  const req = https.request({
    host: process.env.LOCATION_API_HOST,
    path: path,
    method: 'GET'
  }, (response) => {
    let body = '';
    response.on('data', (chunk) => {
      body += chunk;
    });

    response.on('end', () => {
      var data = JSON.parse(body);
      if (data.hasOwnProperty('line') && data.line.hasOwnProperty('coordinates') && data.line.coordinates.length > 0) {
        // Anonymize certain data:
        data.line.coordinates.forEach((coords) => {
          // Anything in Austin is at the Capitol
          if (
            30.1457209625174 < coords[1] &&
            30.427361303226743 > coords[1] &&
            -97.92835235595705 < coords[0] &&
            -97.58090972900392 > coords[0]
          ) {
            coords[0] = -97.74053500;
            coords[1] = 30.27418300;
          }
          // Anything in Tulsa is at the Center of the Universe
          else if (-96.0071182 < coords[0] && 36.1655966 > coords[1] && -95.7616425 > coords[0] && 35.9557765 < coords[1]) {
            coords[0] = -95.99151600;
            coords[1] = 36.15685900;
          }
        });
        // Strip out inline duplicates (i.e. don't remove duplicates, but
        // skip adjacent check-ins with identical coords
        var simplified = data.line.coordinates.filter((el, index, array) => {
          if (index < 2) { return true; }
          return (array[index][0] !== array[index - 1][0]) && (array[index][1] !== array[index - 1][1]);
        });
        data.line.coordinates = simplified;
      }
      else if (typeof data.lon !== 'undefined' && typeof data.lat !== 'undefined') {
        // Anything in Austin is at the Capitol
        if (
          30.1457209625174 < data.lat &&
          30.427361303226743 > data.lat &&
          -97.92835235595705 < data.lon &&
          -97.58090972900392 > data.lon
        ) {
          data.lon = -97.74053500;
          data.lat = 30.27418300;
        }
        // Anything in Tulsa is at the Center of the Universe
        else if (-96.0071182 < data.lon && 36.1655966 > data.lat && -95.7616425 > data.lon && 35.9557765 < data.lat) {
          data.lon = -95.99151600;
          data.lat = 36.15685900;
        }
      }
      
      var responseHeaders = {
        'Access-Control-Allow-Headers': 'Content-type',
        'Access-Control-Allow-Methods': 'GET,HEAD,OPTIONS',
        'Access-Control-Allow-Origin': '*'
      };

      // If we pulled a location via the latest or timestamp endpoints, verify
      // that the response is during a trip mentioned on the blog, otherwise
      // deny the request because we aren't traveling.
      if (verifyTrip && (typeof data.trips === 'undefined' || data.trips.length < 1)) {
        var proxyResponse = {
          statusCode: 403,
          headers: responseHeaders,
          body: JSON.stringify({error: 'No valid trip for this time'})
        };
        callback(null, proxyResponse);
      }
      else {
        var proxyResponse = {
          statusCode: 200,
          headers: responseHeaders,
          body: JSON.stringify(data)
        };
        callback(null, proxyResponse);
      }
    });
  });
  req.end();
};
