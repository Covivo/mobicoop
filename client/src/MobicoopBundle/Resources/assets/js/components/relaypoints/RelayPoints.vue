<template>
  <v-container>
    <v-row
      justify="center"
    >
      <v-col
        cols="12"
        lg="9"
        md="10"
        xl="6"
        class="mt-12"
      >
        <h1 class="display-1 text-center font-weight-bold">
          {{ $t('title') }}
        </h1>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <m-map
          ref="mmap"
          :points="pointsToMap"
          :provider="mapProvider"
          :url-tiles="urlTiles"
          :attribution-copyright="attributionCopyright"
          :markers-draggable="false"
          :relay-points="true"
          @SelectedAsDestination="selectedAsDestination"
          @SelectedAsOrigin="selectedAsOrigin"
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
        <h3 class="headline text-center font-weight-bold">
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
      />
    </v-row>
      
    <!--solidary-form-->
  </v-container>
</template>

<script>
import axios from "axios";
import Translations from "@translations/components/relayPoints/RelayPoints.json";
import Search from "@components/carpool/search/Search";
import MMap from "@components/utilities/MMap"
import L from "leaflet";


export default {
  i18n: {
    messages: Translations
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
      // add relay point address to display on the map
      if (this.relayPointsToMap.length > 0) {
        this.relayPointsToMap.forEach(relayPoint => {
          this.pointsToMap.push(this.buildPoint(relayPoint.address.latitude,relayPoint.address.longitude,relayPoint.name,relayPoint.address));
        });
      }
      this.$refs.mmap.redrawMap();
    },
    buildPoint: function(lat,lng,title="",address=""){
      let point = {
        title:title,
        latLng:L.latLng(lat, lng),
        icon: {},
        address:address
      };
      return point;
    },
    selectedAsDestination(destination) {
      console.error(destination);
      this.selectedDestination = destination;
    },
    selectedAsOrigin(origin) {
      console.error(origin);
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


