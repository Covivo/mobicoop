<template>
  <!-- Start of cartography experiment -->
  <v-row>
    <v-col class="col-12">
      <l-map
        ref="mmap2"
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
  mounted() {
  },  
  methods: {
    redrawMap: function() {
      console.error("redraw !");
      // To redraw the map (when you resize the div you have to redraw the map)
      this.$refs.mmap2.mapObject.invalidateSize();
      this.$refs.mmap2.mapObject.fitBounds([
        [47.413220,-1.219482],
        [47.413220,-1.219482]
      ]);    
    }
  }  
};
</script>