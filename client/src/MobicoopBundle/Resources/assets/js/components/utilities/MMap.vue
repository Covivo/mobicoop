<template>
  <!-- Start of cartography experiment -->
  <v-row>
    <v-col class="col-12">
      <l-map
        ref="mmap"
        :zoom="zoom"
        :center="center"
        style="z-index:1; height:500px;"
      >
        <l-tile-layer
          :url="url"
          :attribution="attributionWithLinks"
        />
        <l-marker
          v-for="(point, index) in points"
          :key="index"
          :lat-lng="point.latLng"
          :draggable="markersDraggable"
          @update:latLng="updateLatLng"
        >
          <l-icon
            v-if="point.icon.url!==undefined"
            :icon-size="point.icon.size"
            :icon-anchor="point.icon.anchor"
            :icon-url="point.icon.url"
          />
          <l-tooltip
            v-if="point.title!==''"
          >
            <p v-html="point.title" />
          </l-tooltip>

          <l-popup v-if="point.popup">
            <h3>{{ point.popup.titre }}</h3>
            <img
              v-if="point.popup.images[0]"
              :src="point.popup.images[0]['versions']['square_100']"
              alt="avatar"
            >
            <p>{{ point.popup.description }}</p>
            <p>{{ point.popup.date_begin }}<br> {{ point.popup.date_end }}</p>
          </l-popup>
        </l-marker>
        <l-polyline
          v-for="(way, i) in ways"
          :key="'w'+i"
          :lat-lngs="way.latLngs"
          :color="(way.color!=='' && way.color !==undefined)?way.color:'blue'"
        >        
          <l-tooltip v-if="way.title !==undefined && way.title!==''">
            <p v-html="way.title" />
          </l-tooltip>
          <l-popup
            v-if="way.desc !==undefined && way.desc!==''"
          >
            <p v-html="way.desc" />
          </l-popup>
        </l-polyline>
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
      default: '{"OpenStreetMap":"http://osm.org/copyright"}'
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
    },
    markersDraggable: {
      type: Boolean,
      default: false
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
  computed: {
    attributionWithLinks(){
      let arrayAttribution = [];
      let jsonAttribution = JSON.parse(this.attribution)
      for(let contributor in jsonAttribution){
        arrayAttribution.push("<a href='"+jsonAttribution[contributor]+"' title=''>"+contributor+"</a>");
      }

      return arrayAttribution.join(', ');
    }
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
        if(bounds.length>0){
          this.$refs.mmap.mapObject.fitBounds(bounds);
        }
      }, 100);
    },
    updateLatLng(data){
      // data containts a LatLng object.
      this.$emit("updateLatLng",data);
    }
  }
};
</script>