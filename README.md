# earthquake-gps

USGS Earthquake Science Center (ESC) GPS stations, data, photos, and logs.

https://earthquake.usgs.gov/monitoring/gps

## Installation

First install [node.js](https://nodejs.org/) and [grunt](http://gruntjs.com).

1. Clone project

```
git clone https://github.com/shaefner-usgs/earthquake-gps.git
```

2. Install dependencies

```
cd earthquake-gps
npm install
```

3. Configure app

```
cd earthquake-gps/src/lib

# Run configuration script and set values that conform to your environment
./pre-install
```

4. Run grunt

```
cd earthquake-gps
grunt
```

5. Open http://localhost:9090/monitoring/gps