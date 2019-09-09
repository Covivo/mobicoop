<template>
  <!-- Start of cartography experiment -->
  <v-row>
    <v-col class="col-6">
      <l-map
        :zoom="zoom"
        :center="center"
        style="height:500px;"
      >
        <l-tile-layer
          :url="url"
          :attribution="attribution"
        />
        <l-marker :lat-lng="marker" />
      </l-map>
    </v-col>
  </v-row>
  <!-- end of cartography experiment -->
</template>

<script>

import L from "leaflet";

export default {
  props: {
  },
  data() {
    return {
      zoom:13,
      center: L.latLng(47.413220, -1.219482),
      url:'http://{s}.tile.osm.org/{z}/{x}/{y}.png',
      attribution:'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
      marker: L.latLng(47.413220, -1.219482)      
    };
  },
};
</script>