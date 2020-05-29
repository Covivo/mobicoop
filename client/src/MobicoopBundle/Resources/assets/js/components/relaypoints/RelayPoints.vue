<template>
  <v-container>
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
        />
      </v-col>
    </v-row>
    <search
      :geo-search-url="geoSearchUrl"
      :user="user"
      :punctual-date-optional="punctualDateOptional"
    />
      
    <!--solidary-form-->
  </v-container>
</template>

<script>
import axios from "axios";
import {merge} from "lodash";
import Translations from "@translations/components/solidary/Solidary.js";
import TranslationsClient from "@clientTranslations/components/solidary/Solidary.js";
import Search from "@components/carpool/search/Search";
import MMap from "@components/utilities/MMap"
import L from "leaflet";


let TranslationsMerged = merge(Translations, TranslationsClient);
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
      directionWay:[]
    }
  },
  mounted() {
    this.getRelayPoints();
  },
  methods:{
    getRelayPoints() {
      axios
        .post('/points-relais/getRelayPointList')
        .then(res => {
          this.relayPointsToMap = res.data;
          this.showRelayPoints();
        });
        
    },
    publish() {
      let lParams = {
        origin: null,
        destination: null,
        regular: this.regular,
        date: null,
        time: null,
        ...this.params
      };
      this.post(`${this.$t("buttons.publish.route")}`, lParams);
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


