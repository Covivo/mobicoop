<template>
  <section class="section">
    <div class="tile is-ancestor">
      <div class="tile is-vertical is-12">
        <div class="tile is-child center-all">
          <div class="columns">
            <b-field class="fieldsContainer">
              <geocomplete
                expanded
                name="origin"
                placeholder="Depuis"
                :url="geoSearchUrl"
                @geoSelected="selectedGeo"
              />
              <geocomplete
                name="destination"
                placeholder="Vers"
                :url="geoSearchUrl"
                @geoSelected="selectedGeo"
              />
              <b-datepicker
                v-model="outwardDate"
                :placeholder="'Date de départ...'"
                :day-names="daysShort"
                :month-names="months"
                :first-day-of-week="1"
                position="is-top-right"
                icon-pack="fas"
              />
              <b-timepicker
                v-model="outwardTime"
                placeholder="Heure de départ..."
              >
                <button
                  class="button is-mobicoopgreen"
                  @click="outwardTime = new Date()"
                >
                  <b-icon icon="clock" />
                  <span>Maintenant</span>
                </button>
                <button
                  class="button is-mobicooppink"
                  @click="outwardTime = null"
                >
                  <b-icon icon="close" />
                  <span>Effacer</span>
                </button>
              </b-timepicker>
              <a
                class="button is-mobicoopblue"
                :href="urlToCall"
                :disabled="!checkUrlValid"
              >
                <b-icon
                  pack="fas"
                  icon="search"
                  size="is-small"
                >
                  />
                </b-icon></a>
            </b-field>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import axios from "axios";
import moment from "moment";
import Geocomplete from "./Geocomplete";
import _default from 'flatpickr/dist/l10n/fr';
export default {
  components: {
    Geocomplete
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: ""
    },
    route: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      daysShort: [
        "Dim",
        "Lun",
        "Mar",
        "Mer",
        "Jeu",
        "Ven",
        "Sam"
      ],
      months: [
        "Janvier",
        "Fevrier",
        "Mars",
        "Avril",
        "Mai",
        "Juin",
        "Juillet",
        "Aout",
        "Septembre",
        "Octobre",
        "Novembre",
        "Décembre"
      ],
      originLatitude: null,
      originLongitude: null,
      destinationLatitude: null,
      destinationLongitude: null,
      outwardDate: null,
      outwardTime: null,
      baseUrl: window.location.origin,
    };
  },
  computed: {
    checkUrlValid(){
      return this.originLatitude && this.originLongitude && this.destinationLatitude && this.destinationLongitude && this.outwardDate && this.outwardTime
    },
    dateFormated() {
      return this.outwardDate ? moment(this.outwardDate).format('YYYYMMDD') : null ;
    },
    timeFormated() {
      return this.outwardTime ? moment(this.outwardTime).format('HHMMSS') : null;
    },
    urlToCall() {
      return this.checkUrlValid != null ? `${this.baseUrl}/${this.route}/${this.originLatitude}/${this.originLongitude}/${this.destinationLatitude}/${this.destinationLongitude}/${this.dateFormated}${this.timeFormated}/resultats` : '#'
    } 
  },

  methods: {
    selectedGeo(val) {
      let name = val.name;
      this[name + "Latitude"] = val.latitude;
      this[name + "Longitude"] = val.longitude;
    },
  }
};
</script>

<style lang="scss" scoped>

.fieldsContainer {
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>