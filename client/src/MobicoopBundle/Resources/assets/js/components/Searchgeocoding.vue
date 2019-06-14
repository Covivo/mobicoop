<template>
  <affix class="card afixSearch" relative-element-selector="#journeyResult">
    <div class="card-content">
      <div class="content">
        <div class="control has-icons-left has-icons-right">
          <!-- TODO, this is not the way to bind a props :s -->
          <input
            v-model="searchUser"
            placeholder="From  To"
            class="input is-large searchUser"
            type="text"
            @keyup.enter="searchGeoFrance"
          >
          <span class="icon is-medium is-left">
            <i class="fa fa-search"/>
          </span>
          <span class="icon is-medium is-right">
            <i class="fa fa-empire"/>
          </span>
        </div>
      </div>
    </div>
  </affix>
</template>
<script>
import axios from "axios";

// This is the main component Journey
export default {
  name: "Searchgeocoding",
  // props can be sent by backend ! 👌
  props: {
    // geoInfos is set in home.js & used by many components
    geoInfos: {
      type: Object,
      default: function() {
        return {};
      }
    },
    searchUser: {
      type: String,
      default: ""
    }
  },
  methods: {
    searchGeoFrance: function() {
      let cities = this.searchUser.split(" "),
        start = cities[0],
        end = cities[1];
      // We shoud have start & end cities
      if (!start || !end) {
        return;
      }
      let urlStart = `https://api-adresse.data.gouv.fr/search/?q=${start}&limit=1`,
        urlEnd = `https://api-adresse.data.gouv.fr/search/?q=${end}&limit=1`;
      axios.all([axios.get(urlStart), axios.get(urlEnd)]).then(
        axios.spread((startInfos, endInfos) => {
          let coordinatesStart =
              startInfos.data.features[0].geometry.coordinates,
            coordinatesEnd = endInfos.data.features[0].geometry.coordinates;
          this.geoInfos.longStart = coordinatesStart[0];
          this.geoInfos.latStart = coordinatesStart[1];
          this.geoInfos.longEnd = coordinatesEnd[0];
          this.geoInfos.latEnd = coordinatesEnd[1];
        })
      );
    }
  }
};
</script>