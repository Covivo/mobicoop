<template>
  <section class="section">
    <div class="tile is-ancestor">
      <div class="tile is-vertical is-12">
        <div class="tile is-child center-all">
          <form-wizard
            @on-complete="onComplete"
            back-button-text="Précèdent"
            next-button-text="Suivant"
            finish-button-text="Je partage mon annonce"
            title="Déposer une annonce"
            subtitle="Suivez les étapes.."
            color="#023D7F"
            class="tile is-vertical is-8"
          >
            <!-- ROLE -->
            <tab-content title="Vous êtes" icon="fa fa-user-friends" class="tabContent">
              <h3>Je suis:</h3>
              <b-field class="fieldsContainer">
                <b-radio-button
                  v-model="form.role"
                  name="role"
                  :native-value="1"
                  type="is-mobicoopblue"
                >
                  <b-icon icon="close"></b-icon>
                  <span>🚙 Conducteur</span>
                </b-radio-button>
                <b-radio-button
                  v-model="form.role"
                  name="role"
                  :native-value="2"
                  type="is-mobicooppink"
                >
                  <b-icon icon="check"></b-icon>
                  <span>👨‍⚖️ Passager</span>
                </b-radio-button>
                <b-radio-button
                  v-model="form.role"
                  name="role"
                  :native-value="3"
                  type="is-mobicoopgreen"
                >Passager ou Conducteur</b-radio-button>
              </b-field>
            </tab-content>
            <!-- TYPE TRAJET -->
            <tab-content title="Trajet" icon="fa fa-route" class="tabContent">
              <h3>Détails de votre trajet</h3>
              <b-field class="fieldsContainer">
                <b-radio-button
                  v-model="form.type"
                  name="type"
                  :native-value="1"
                  type="is-mobicoopblue"
                >
                  <b-icon pack="fas" icon="long-arrow-alt-right"></b-icon>
                  <span>Allez</span>
                </b-radio-button>
                <b-radio-button
                  v-model="form.type"
                  name="type"
                  :native-value="2"
                  type="is-mobicoopblue"
                >
                  <b-icon pack="fas" icon="exchange-alt"></b-icon>
                  <span>Allez/Retour</span>
                </b-radio-button>
              </b-field>
              <geocomplete
                name="origin"
                placeholder="Depuis"
                :url="geoSearchUrl"
                @geoSelected="selectedGeo"
              ></geocomplete>
              <b-icon
                pack="fas"
                type="is-mobicoopblue"
                :icon="form.type === 2 ? 'arrows-alt-v' : 'long-arrow-alt-down'"
                size="is-large"
              ></b-icon>
              <geocomplete
                placeholder="Vers"
                :url="geoSearchUrl"
                name="destination"
                @geoSelected="selectedGeo"
              ></geocomplete>
            </tab-content>
            <!-- FREQUENCY & Submit -->
            <tab-content title="Fréquence" icon="fa fa-calendar-check" class="tabContent">
              <h3>Fréquence du trajet:</h3>
              <b-field class="fieldsContainer">
                <b-radio-button
                  v-model="form.frequency"
                  name="frequency"
                  :native-value="1"
                  type="is-mobicoopblue"
                >
                  <b-icon icon="close"></b-icon>
                  <span>Ponctuel</span>
                </b-radio-button>
                <b-radio-button
                  v-model="form.frequency"
                  name="frequency"
                  :native-value="2"
                  type="is-mobicoopblue"
                >
                  <b-icon icon="check"></b-icon>
                  <span>Regulier</span>
                </b-radio-button>
              </b-field>
              <!-- DATE, TIME , MARGIN -->
              <!-- REGULAR -->
              <div v-if="form.frequency === 2">
                <div class="columns" v-for="(day,index) in days" :key="index">
                  <div class="column">
                    <h5 class="title">Aller ({{day}})</h5>
                    <b-datepicker placeholder="Date de départ..." icon="calendar-today"></b-datepicker>
                    <b-timepicker v-model="timeStart" placeholder="Heure de départ...">
                      <button class="button is-primary" @click="time = new Date()">
                        <b-icon icon="clock"></b-icon>
                        <span>Maintenant</span>
                      </button>
                      <button class="button is-danger" @click="time = null">
                        <b-icon icon="close"></b-icon>
                        <span>Effacer</span>
                      </button>
                    </b-timepicker>
                  </div>
                  <div class="column" v-if="type === 2">
                    <h5 class="title">Retour ({{day}})</h5>
                    <b-datepicker placeholder="Date de retour..." icon="calendar-today"></b-datepicker>
                    <b-timepicker v-model="timeReturn" placeholder="heure de retour...">
                      <button class="button is-primary" @click="time = new Date()">
                        <b-icon icon="clock"></b-icon>
                        <span>Maintenant</span>
                      </button>
                      <button class="button is-danger" @click="time = null">
                        <b-icon icon="close"></b-icon>
                        <span>Effacer</span>
                      </button>
                    </b-timepicker>
                  </div>
                </div>
              </div>
              <!-- PONCTUAL -->
              <div class="columns" v-else>
                <div class="column">
                  <h5 class="title column is-full">Aller</h5>
                  <b-datepicker
                    placeholder="Date de départ..."
                    v-model="form.outwardDate"
                    :day-names="daysShort"
                    :month-names="months"
                    :first-day-of-week="1"
                    class="column is-full"
                    position="is-top-right"
                    icon-pack="fas"
                  ></b-datepicker>
                  <div class="column is-full">
                    <div class="columns">
                      <b-timepicker
                        class="column is-8"
                        v-model="form.outwardTime"
                        placeholder="Heure de départ..."
                      >
                        <button
                          class="button is-mobicoopgreen"
                          @click="form.outwardTime = new Date()"
                        >
                          <b-icon icon="clock"></b-icon>
                          <span>Maintenant</span>
                        </button>
                        <button class="button is-mobicooppink" @click="form.outwardTime = null">
                          <b-icon icon="close"></b-icon>
                          <span>Effacer</span>
                        </button>
                      </b-timepicker>
                      <b-select
                        class="column is-4"
                        placeholder="Marge"
                        v-model="form.outwardMargin"
                      >
                        <option
                          v-for="(margin,key) in marginsMn"
                          :value="margin"
                          :key="key"
                        >{{ (1 > margin/60 > 0) ? margin : `${Math.trunc(margin/60)}H${margin%60}` }}</option>
                      </b-select>
                    </div>
                  </div>
                </div>
                <!-- RETURN -->
                <div class="column" v-if="form.type === 2">
                  <h2 class="title column is-full">Retour</h2>
                  <b-datepicker
                    placeholder="Date de retour..."
                    icon="calendar-today"
                    class="column is-full"
                  ></b-datepicker>
                  <b-timepicker
                    v-model="timeReturn"
                    placeholder="heure de retour..."
                    class="column is-full"
                  >
                    <button class="button is-mobicoopgreen" @click="time = new Date()">
                      <b-icon icon="clock"></b-icon>
                      <span>Maintenant</span>
                    </button>
                    <button class="button is-mobicooppink" @click="time = null">
                      <b-icon icon="close"></b-icon>
                      <span>Effacer</span>
                    </button>
                  </b-timepicker>
                </div>
              </div>
            </tab-content>
          </form-wizard>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import axios from "axios";
import Geocomplete from "./Geocomplete";
export default {
  components: {
    Geocomplete
  },
  props: {
    sentFrequency: {
      type: Number,
      default: 1
    },
    sentRole: {
      type: Number,
      default: 1
    },
    sentType: {
      type: Number,
      default: 1
    },
    geoSearchUrl: {
      type: String,
      default: ""
    },
    sentOutward: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      origin: null,
      outward: this.sentOutward,
      timeStart: new Date(),
      timeReturn: new Date(),
      days: [
        "dimanche",
        "lundi",
        "mardi",
        "mercredi",
        "jeudi",
        "vendredi",
        "samedi"
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
      // margins in minutes
      marginsMn: [5, 10, 15, 20, 25, 30, 45, 60, 90, 120, 150],
      form: {
        origin: "",
        originLatitude: null,
        originLongitude: null,
        destinationLatitude: null,
        destinationLongitude: null,
        destination: "",
        role: this.sentRole,
        type: this.sentType,
        frequency: this.sentFrequency,
        outwardDate: null,
        outwardMargin: null,
        outwardTime: null
      }
    };
  },
  computed: {
    daysShort() {
      return this.days.map(day => day.substring(0, 2));
    }
  },
  methods: {
    selectedGeo(val) {
      let name = val.name;
      this.form[name] = `${val.streetAddress ? val.streetAddress + " " : ""}${
        val.addressLocality
      } ${val.addressCountry}`;
      this.form[name + "Latitude"] = val.latitude;
      this.form[name + "Longitude"] = val.longitude;

      // this.form.destination = `${val.streetAddress ? streetAddress+' ': ''}${val.addressLocality} ${val.addressCountry}`;
      // this.form.destinationLongitude = val.longitude;
      // this.form.destinationLatitude = val.latitude;
    },
    sendForm() {
      console.log("Will send form");
    },
    onComplete() {
      let adForm = new FormData();
      for (let prop in this.form) {
        console.log(prop, this.form[prop]);
        adForm.append(prop, this.form[prop]);
      }
      console.log(adForm);
      axios
        .post("/covoiturage/annonce/poster", adForm, {
          headers: {
            "Content-Type": "multipart/form-data"
          }
        })
        .then(function(response) {
          console.log(response);
        })
        .catch(function(error) {
          console.error(error);
        });
    }
  }
};
</script>

<style lang="scss" scoped>
.tabContent {
  text-align: center;
}

.fieldsContainer {
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
