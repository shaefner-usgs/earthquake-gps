#! /usr/bin/env python

import json
import requests
import sys
import urllib.parse as urlparse


CNAMES = {
    'earthquake.usgs.gov': [
        'earthquake.usgs.gov',
        'prod-earthquake.cr.usgs.gov',
        'prod01-earthquake.cr.usgs.gov',
        'prod02-earthquake.cr.usgs.gov'
    ],

    'dev-earthquake.cr.usgs.gov': [
        'earthquake.usgs.gov',
        'dev-earthquake.cr.usgs.gov',
        'dev01-earthquake.cr.usgs.gov',
        'dev02-earthquake.cr.usgs.gov'
    ],

    'geomag.usgs.gov': [
        'geomag.usgs.gov',
        'prod-geomag.cr.usgs.gov',
        'prod01-geomag.cr.usgs.gov',
        'prod02-geomag.cr.usgs.gov'
    ],

    'dev-geomag.cr.usgs.gov': [
        'geomag.usgs.gov',
        'dev-geomag.cr.usgs.gov',
        'dev01-geomag.cr.usgs.gov',
        'dev02-geomag.cr.usgs.gov'
    ],

    'landslides.usgs.gov': [
        'landslides.usgs.gov',
        'prod-landslides.cr.usgs.gov',
        'prod01-landslides.cr.usgs.gov',
        'prod02-landslides.cr.usgs.gov'
    ],

    'dev-landslides.cr.usgs.gov': [
        'landslides.usgs.gov',
        'dev-landslides.cr.usgs.gov',
        'dev01-landslides.cr.usgs.gov',
        'dev02-landslides.cr.usgs.gov'
    ],

}

DEV_CACHES = [
    'dev01-cache01.cr.usgs.gov',
    'dev01-cache02.cr.usgs.gov',
    'dev02-cache01.cr.usgs.gov',
    'dev02-cache02.cr.usgs.gov',
]

PROD_CACHES = [
    'prod01-cache01.cr.usgs.gov',
    'prod01-cache02.cr.usgs.gov',
    'prod02-cache01.cr.usgs.gov',
    'prod02-cache02.cr.usgs.gov',
]


def invalidate(url):
    status = []
    parsed = urlparse.urlparse(url)
    # figure out which cnames and cache servers to invalidate
    hostname = parsed.hostname
    cnames = hostname in CNAMES and CNAMES[hostname] or [hostname]
    caches = hostname.startswith('dev-') and DEV_CACHES or PROD_CACHES
    # now inalidate
    for cache in caches:
        cache_url = url.replace(parsed.hostname, cache, 1)
        for cname in cnames:
            r = requests.get(
                    cache_url,
                    headers = {
                        'host': cname,
                        'X-Purge': 'PURGE'
                    },
                    verify=False)
            status.append({
                'cache_server': cache,
                'cname': cname,
                'status_code': r.status_code
            })
    return status


if __name__ == '__main__':
    urls = sys.argv[1:]
    for url in urls:
        print('Invalidating', url, file=sys.stderr)
        status = invalidate(url)
        print(json.dumps(status, indent=4))


