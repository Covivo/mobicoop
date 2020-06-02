<template>
  <v-container>
    <v-row
      justify="left"
    >
      <v-col
        cols="12"
        lg="9"
        md="10"
        xl="6"
        class="mt-6"
      >
        <h1 class="display-1 text-left font-weight-bold">
          {{ $t('title') }}
        </h1>
      </v-col>
    </v-row>
    <v-row
      justify="center"
    >
      <v-col>
        <m-map
          ref="mmap"
          type-map="community"
          :points="pointsToMap"
          :provider="mapProvider"
          :url-tiles="urlTiles"
          :attribution-copyright="attributionCopyright"
          :markers-draggable="false"
          :relay-points="true"
          @SelectedAsDestination="tada"
          @SelectedAsOrigin="tidi"
        />
      </v-col>
    </v-row>
    <v-row
      justify="center"
    >
      <v-col
        cols="12"
        lg="9"
        md="10"
        xl="6"
        class="mt-6"
      >
        <h3 class="headline text-justify font-weight-bold">
          {{ $t('search') }}
        </h3>
      </v-col>
    </v-row>
    <v-row
      justify="center"
    >
      <search
        :default-origin="selectedOrigin"
        :default-destination="selectedDestination"
        :geo-search-url="geoSearchUrl"
        :user="user"
        :punctual-date-optional="punctualDateOptional"
      />
    </v-row>
      
    <!--solidary-form-->
  </v-container>
</template>

<script>
import axios from "axios";
import {merge} from "lodash";
import Translations from "@translations/components/relayPoints/RelayPoints.json";
import ClientTranslations from "@clientTranslations/components/relayPoints/RelayPoints.json";
import Search from "@components/carpool/search/Search";
import MMap from "@components/utilities/MMap"
import L from "leaflet";


let TranslationsMerged = merge(Translations, ClientTranslations);
export default {
  i18n: {
    messages: TranslationsMerged
  },
  components: {
    Search, MMap
  },
  props:{
    user: {
      type: Object,
      default: null
    },
    geoSearchUrl: {
      type: String,
      default: ""
    },
    punctualDateOptional: {
      type: Boolean,
      default: false
    },
    mapProvider:{
      type: String,
      default: ""
    },
    urlTiles:{
      type: String,
      default: ""
    },
    attributionCopyright:{
      type: String,
      default: ""
    },
    urlAdmin: {
      type: String,
      default: null
    }
  },
  data () {
    return {
      search: '',
      relayPointsToMap: null,
      pointsToMap:[],
      directionWay:[],
      selectedDestination: null,
      selectedOrigin: null
    }
  },
  mounted() {
    this.getRelayPoints();
  },
  methods:{
    getRelayPoints() {
      axios
        .post(this.$t("relayPointList"))
        .then(res => {
          this.relayPointsToMap = res.data;
          this.showRelayPoints();
        });
        
    },
    showRelayPoints () {
      this.pointsToMap.length = 0;
      // add the community address to display on the map
      if (this.relayPointsToMap.length > 0) {
        this.relayPointsToMap.forEach(relayPoint => {
          this.pointsToMap.push(this.buildPoint(relayPoint.lat,relayPoint.lon,relayPoint.name));
        });
      }
      this.$refs.mmap.redrawMap();
    },
    buildPoint: function(lat,lng,title="",pictoUrl="",size=[],anchor=[],popupDesc=""){
      let point = {
        title:title,
        latLng:L.latLng(lat, lng),
        icon: {},
      };

      if(pictoUrl!==""){
        point.icon = {
          url:pictoUrl,
          size:size,
          anchor:anchor
        }
      }
      if(popupDesc!==""){
        point.popup = {
          title:title,
          description:popupDesc
        }
      }
      return point;
    },
    searchMatchings(){
      console.error("searchMatchings");
    },
    tada(data) {
      console.error('destination');
      console.error(data.address);
      this.selectedDestination = destination;
    },
    tidi(LatLng) {
      console.error("origine"); 
      console.error(LatLng.address);
      this.selectedOrigin = origin;
    }
  }
}
</script>

<style lang="scss" scoped>
.multiline {
  padding:20px;
  white-space: normal;
}
.vue2leaflet-map {
    z-index: 1;
}

</style>


