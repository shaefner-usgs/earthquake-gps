/* global MOUNT_PATH, STATION */ // passed via var embedded in html page
'use strict';


var Application = require('qc/Application');

var app,
    el;

el = document.querySelector('#application');

app = Application({
  // channel metadata
  'channels': {
    'date': {
      'title': 'Date'
    },
    'mp1': {
      'title': 'MP1',
      'units': 'meters'
    },
    'mp2': {
      'title': 'MP2',
      'units': 'meters'
    },
    'percentage': {
      'title': 'Percentage'
    },
    'slips_per_obs': {
      'title': 'Slips per Observation',
    },
    'sn1': {
      'title': 'SN1',
      'units': 'dB-Hz'
    },
    'sn2': {
      'title': 'SN2',
      'units': 'dB-Hz'
    }
  },

  // app element
  'el': el,

  // list of plots
  'plots': [
    {
      'title': '<h2>Completeness</h2>',
      'channels': [
        'percentage',
        'slips_per_obs'
      ]
    },
    {
      'title': '<h2>Multipath</h2>',
      'channels': [
        'mp1',
        'mp2'
      ]
    },
    {
      'title': '<h2>Signal-to-Noise</h2>',
      'channels': [
        'sn1',
        'sn2'
      ]
    }
  ],

  // qc data url
  'url': MOUNT_PATH + '/_getQcData.json.php?station=' + STATION
});
