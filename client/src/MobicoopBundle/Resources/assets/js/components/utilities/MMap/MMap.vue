<template>
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
        <!-- Markers in clusters -->
        <v-marker-cluster
          v-if="clusters"
          :options="clusterOptions"
        >
          <m-marker
            v-for="(point, index) in points"
            :key="index"
            :point="point"
            :markers-draggable="markersDraggable"
            @updateLatLng="updateLatLng"
            @clickOnPoint="clickOnPoint(point.address)"
          />
        </v-marker-cluster>
        <!-- Only markers, no cluster -->
        <m-marker
          v-for="(point, index) in points"
          v-else
          :key="index"
          :point="point"
          :color="point.color"
          :markers-draggable="markersDraggable"
          :circle-marker="(point.circleMarker) ? point.circleMarker : false"
          @updateLatLng="updateLatLng"
          @clickOnPoint="clickOnPoint(point.address)"
        />
        <v-dialog
          v-model="dialog"
          max-width="400"
        >
          <v-card>
            <v-card-title class="text-h6 justify-center">
              {{ $t('dialog.title') }}
            </v-card-title>
            <v-card-actions class="justify-center">
              <v-btn
                class="ml-8"
                color="primary"
                text
                @click="selectRelayPointAsOrigin "
              >
                {{ $t('dialog.origin') }}
              </v-btn>
              <v-btn
                color="primary"
                text
                @click="selectRelayPointAsDestination"
              >
                {{ $t('dialog.destination') }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <l-polyline
          v-for="(way, i) in ways"
          :key="'w'+i"
          :lat-lngs="way.latLngs"
          :color="(way.color!=='' && way.color !==undefined)?way.color:'blue'"
          :dash-array="(way.dashArray) ? way.dashArray : ''"
          @click="clickOnPolyline"
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
</template>

<script>
import L from "leaflet";
import VMarkerCluster from 'vue2-leaflet-markercluster'
import MMarker from "@components/utilities/MMap/MMarker"
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/MMap/MMap";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components: {
    VMarkerCluster,
    MMarker
  },
  props: {
    provider: {
      type: String,
      default: "OpenStreetMap"
    },
    urlTiles: {
      type: String,
      default: "https://{s}.tile.osm.org/{z}/{x}/{y}.png"
    },
    providerKey: {
      // unused for the moment
      type: String,
      default: ""
    },
    attributionCopyright: {
      type: String,
      default: '{"OpenStreetMap":"https://osm.org/copyright"}'
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
    },
    relayPoints: {
      type: Boolean,
      default: false
    },
    clusters: {
      type: Boolean,
      default: true
    },
    dashArray:{
      type: String,
      default: null
    },
    defaultMapBounds: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      center: L.latLng(this.centerDefault[0], this.centerDefault[1]),
      url:this.urlTiles,
      attribution:this.attributionCopyright,
      markers:this.points,
      dialog: false,
      address: null,
      clusterOptions: {}
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
  watch: {
    defaultMapBounds (bounds) {
      if (bounds && this.points.length === 0) {
        this.$refs.mmap.mapObject.fitBounds(bounds);
      }
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
        if(bounds.length === 0 && this.defaultMapBounds) {
          bounds = this.defaultMapBounds;
        }
        if (bounds.length > 0) {
          this.$refs.mmap.mapObject.fitBounds(bounds);
        }
      }, 100);
    },
    updateLatLng(data){
      // data contains a LatLng object.
      this.$emit("updateLatLng",data);
    },
    clickOnPolyline(data){
      // data contains a LatLng object.
      this.$emit("clickOnPolyline",data);
    },
    clickOnPoint(point){
      if (this.relayPoints) {
        this.dialog = true;
        this.address = point;
      }
    },
    selectRelayPointAsOrigin() {
      if (this.relayPoints) {
        this.$emit("SelectedAsOrigin",this.address);
        this.address= null;
        this.dialog= false;
      }
    },
    selectRelayPointAsDestination() {
      if (this.relayPoints) {
        this.$emit("SelectedAsDestination",this.address);
        this.address= null;
        this.dialog= false;

      }
    }
  }
};
</script>
<style lang="scss" scoped>
#description-tooltip{
    width: auto;
    max-width: 200px;
    overflow:hidden;
    text-overflow: ellipsis;
}
</style>
