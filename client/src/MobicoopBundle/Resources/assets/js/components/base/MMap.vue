<template>
  <!-- Start of cartography experiment -->
  <v-row>
    <v-col class="col-12">
      <l-map
        ref="mmap"
        :zoom="zoom"
        :center="center"
        style="height:500px;"
      >
        <l-tile-layer
          :url="url"
          :attribution="attribution"
        />
        <l-marker
          v-for="(point, index) in points"
          :key="index"
          :lat-lng="point.latLng"
        >
          <l-icon
            v-if="point.icon.url!==undefined"
            :icon-size="point.icon.size"
            :icon-anchor="point.icon.anchor"
            :icon-url="point.icon.url"
          />
        </l-marker>
      </l-map>
    </v-col>
  </v-row>
  <!-- end of cartography experiment -->
</template>

<script>
import L from "leaflet";

export default {
  props: {
    provider: {
      type: String,
      default: "OpenStreetMap"
    },
    urlTiles: {
      type: String,
      default: "http://{s}.tile.osm.org/{z}/{x}/{y}.png"
    },
    providerKey: {
      // unused for the moment
      type: String,
      default: ""
    },
    attributionCopyright: {
      type: String,
      default: "&copy; <a href='http://osm.org/copyright'>OpenStreetMap</a> contributors"
    },
    centerDefault: {
      type: Array,
      default: function(){return [];}
    },
    zoom: {
      type: Number,
      default: 13
    },    
    typeMap: {
      type: String,
      default: ""
    },
    points: {
      type: Array,
      default: function(){return [];}
    },
    ways: {
      type: Array,
      default: function(){return [];}
    }
  },
  data() {
    return {
      center: L.latLng(this.centerDefault[0], this.centerDefault[1]),
      url:this.urlTiles,
      attribution:this.attributionCopyright,
      markers:this.points
    };
  },
  mounted() {
    //this.showPoints();
  },
  methods: {
    redrawMap: function() {
      // To redraw the map (when you resize the div you have to redraw the map)
      setTimeout(() => {
        this.$refs.mmap.mapObject.invalidateSize();

        // I'm using all points to set the boundaries
        let bounds = [];
        this.points.forEach((pointForBound, index) => {
          bounds.push([pointForBound.latLng.lat,pointForBound.latLng.lng]);
        });
        this.$refs.mmap.mapObject.fitBounds(bounds);


      }, 100);
    }
  }
};
</script>