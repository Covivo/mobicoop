<template>
  <section
    id="adCreateForm"
    class="section"
  >
    <div class="tile is-ancestor">
      <div class="tile is-vertical is-12">
        <div class="tile is-child center-all">
          <form-wizard
            back-button-text="Précédent"
            next-button-text="Suivant"
            finish-button-text="Je partage mon annonce"
            title="Partager une annonce"
            subtitle="Suivez les étapes.."
            color="#023D7F"
            class="tile is-vertical is-8"
            @on-complete="onComplete"
          >
            <!-- ROLE -->
            <tab-content
              title="Vous êtes"
              icon="fa fa-user-friends"
              class="tabContent"
            >
              <h3>Je suis:</h3>
              <b-field class="fieldsContainer">
                <b-radio-button
                  v-model="form.role"
                  name="role"
                  :native-value="1"
                  type="is-secondary"
                >
                  <b-icon icon="close" />
                  <span>🚙 Conducteur</span>
                </b-radio-button>
                <b-radio-button
                  v-model="form.role"
                  name="role"
                  :native-value="2"
                  type="is-tertiary"
                >
                  <b-icon icon="check" />
                  <span>👨‍⚖️ Passager</span>
                </b-radio-button>
                <b-radio-button
                  v-model="form.role"
                  name="role"
                  :native-value="3"
                  type="is-primary"
                >
                  Passager ou Conducteur
                </b-radio-button>
                <b-select
                  v-if="!idCommunity"
                  v-model="form.community"
                  name="selectCommunity"
                  :native-value="4"
                  type="is-primary"
                >
                  <option
                    v-for="(community,index) in communities"
                    :key="index"
                    :value="index"
                  >
                    {{ community }}
                  </option>
                </b-select>
                <b-radio-button
                  v-else
                  v-model="form.idCommunity"
                  name="role"
                  :native-value="5"
                  type="is-primary"
                >
                  {{ communities[idCommunity] }}
                </b-radio-button>
              </b-field>
            </tab-content>
            <!-- TYPE TRAJET -->
            <tab-content
              title="Trajet"
              icon="fa fa-route"
              class="tabContent"
            >
              <h3>Détails de votre trajet</h3>
              <b-field class="fieldsContainer">
                <b-radio-button
                  v-model="form.type"
                  name="type"
                  :native-value="1"
                  type="is-secondary"
                >
                  <b-icon
                    pack="fas"
                    icon="long-arrow-alt-right"
                  />
                  <span>Aller</span>
                </b-radio-button>
                <b-radio-button
                  v-model="form.type"
                  name="type"
                  :native-value="2"
                  type="is-secondary"
                >
                  <b-icon
                    pack="fas"
                    icon="exchange-alt"
                  />
                  <span>Aller-retour</span>
                </b-radio-button>
              </b-field>
              <geocomplete
                name="origin"
                placeholder="Depuis"
                :url="geoSearchUrl"
                @geoSelected="selectedGeo"
              />
              <b-icon
                pack="fas"
                type="is-secondary"
                :icon="form.type === 2 ? 'arrows-alt-v' : 'long-arrow-alt-down'"
                size="is-large"
              />
              <GeoComplete
                placeholder="Vers"
                :url="geoSearchUrl"
                name="destination"
                @geoSelected="selectedGeo"
              />
            </tab-content>
            <!-- FREQUENCY & Submit -->
            <tab-content
              title="Fréquence"
              icon="fa fa-calendar-check"
              class="tabContent"
            >
              <h3>Fréquence du trajet:</h3>
              <b-field class="fieldsContainer">
                <b-radio-button
                  v-model="form.frequency"
                  name="frequency"
                  :native-value="1"
                  type="is-secondary"
                >
                  <b-icon icon="close" />
                  <span>Ponctuel</span>
                </b-radio-button>
                <b-radio-button
                  v-model="form.frequency"
                  name="frequency"
                  :native-value="2"
                  type="is-secondary"
                >
                  <b-icon icon="check" />
                  <span>Régulier</span>
                </b-radio-button>
              </b-field>
              <!-- DATE, TIME , MARGIN -->
              <div class="columns">
                <!-- Punctual one way Trip -->
                <div
                  v-if="form.frequency ===1"
                  class="column"
                >
                  <h5 class="title column is-full">
                    Aller
                  </h5>
                  <b-datepicker
                    v-model="form.outwardDate"
                    :placeholder="'Date de départ...'"
                    :day-names="daysShort"
                    :month-names="months"
                    :first-day-of-week="1"
                    class="column is-full"
                    position="is-top-right"
                    icon-pack="fas"
                  />
                  <div class="column is-full">
                    <div
                      v-for="(day,index) in nbOfDaysToPlan"
                      :key="index"
                      class="columns"
                    >
                      <div
                        v-if="nbOfDaysToPlan>1"
                        class="column is-2 dayNameColumn"
                      >
                        <a class="button is-secondary is-2">{{ days[index] }}</a>
                      </div>
                      <b-timepicker
                        v-model="form.outwardTime"
                        class="column"
                        placeholder="Heure de départ..."
                      >
                        <button
                          class="button is-primary"
                          @click="form.outwardTime = new Date()"
                        >
                          <b-icon icon="clock" />
                          <span>Maintenant</span>
                        </button>
                        <button
                          class="button is-tertiary"
                          @click="form.outwardTime = null"
                        >
                          <b-icon icon="close" />
                          <span>Effacer</span>
                        </button>
                      </b-timepicker>
                      <!-- MARGIN -->
                      <b-select
                        v-model="form.outwardMargin"
                        class="column is-4"
                        placeholder="Marge"
                      >
                        <option
                          v-for="(margin,key) in marginsMn"
                          :key="key"
                          :value="margin"
                        >
                          {{ (1 > margin/60 > 0) ? margin : `${Math.trunc(margin/60)}H${margin%60}` }}
                        </option>
                      </b-select>
                    </div>
                  </div>
                </div>
                <!-- Regular one way trip-->
                <div
                  v-if="form.frequency ===2"
                  class="column"
                >
                  <h5 class="title column is-full">
                    Aller
                  </h5>
                  <b-datepicker
                    v-model="form.fromDate"
                    :placeholder="'Date de début'"
                    :day-names="daysShort"
                    :month-names="months"
                    :first-day-of-week="1"
                    class="column is-full"
                    position="is-top-right"
                    icon-pack="fas"
                  />
                  <b-datepicker
                    v-if="form.type ===1"
                    v-model="form.toDate"
                    :placeholder="'Date de fin'"
                    :day-names="daysShort"
                    :month-names="months"
                    :first-day-of-week="1"
                    class="column is-full"
                    position="is-top-right"
                    icon-pack="fas"
                  />

                  <div class="column is-full">
                    <div
                      v-for="(day,index) in nbOfDaysToPlan"
                      :key="index"
                      class="columns"
                    >
                      <div
                        v-if="nbOfDaysToPlan>1"
                        class="column is-2 dayNameColumn"
                      >
                        <a class="button is-secondary is-2">{{ days[index] }}</a>
                      </div>
                      <b-timepicker
                        v-model="form['outward'+daysShort[index]+'Time']"
                        class="column"
                        placeholder="Heure de départ..."
                      >
                        <button
                          class="button is-primary"
                          @click="form['outward'+daysShort[index]+'Time']= new Date()"
                        >
                          <b-icon icon="clock" />
                          <span>Maintenant</span>
                        </button>
                        <button
                          class="button is-tertiary"
                          @click="form['outward'+daysShort[index]+'Time'] = null"
                        >
                          <b-icon icon="close" />
                          <span>Effacer</span>
                        </button>
                      </b-timepicker>
                      <!-- MARGIN -->
                      <b-select
                        v-model="form['outward'+daysShort[index]+'Margin']"
                        class="column is-4"
                        placeholder="Marge"
                      >
                        <option
                          v-for="(margin,key) in marginsMn"
                          :key="key"
                          :value="margin"
                        >
                          {{ (1 > margin/60 > 0) ? margin : `${Math.trunc(margin/60)}H${margin%60}` }}
                        </option>
                      </b-select>
                    </div>
                  </div>
                </div>

                <!-- RETURN Punctual trip-->
                <div
                  v-if="form.type === 2 && form.frequency === 1"
                  class="column"
                >
                  <h2 class="title column is-full">
                    Retour
                  </h2>
                  <b-datepicker
                    v-model="form.returnDate"
                    :placeholder="'Date de retour...'"
                    icon="calendar-today"
                    class="column is-full"
                  />
                  <div
                    class="columns"
                  >
                    <b-timepicker
                      v-model="form.returnTime"
                      placeholder="heure de retour..."
                      class="column"
                    >
                      <button
                        class="button is-primary"
                        @click="form.returnTime = new Date()"
                      >
                        <b-icon icon="clock" />
                        <span>Maintenant</span>
                      </button>
                      <button
                        class="button is-tertiary"
                        @click="form.returnTime = null"
                      >
                        <b-icon icon="close" />
                        <span>Effacer</span>
                      </button>
                    </b-timepicker>
                    <!-- MARGIN -->
                    <b-select
                      v-model="form.returnMargin"
                      class="column is-4"
                      placeholder="Marge"
                    >
                      <option
                        v-for="(margin,key) in marginsMn"
                        :key="key"
                        :value="margin"
                      >
                        {{ (1 > margin/60 > 0) ? margin : `${Math.trunc(margin/60)}H${margin%60}` }}
                      </option>
                    </b-select>
                  </div>
                </div>
                <!-- RETURN Regular trip-->
                <div
                  v-if="form.type === 2 && form.frequency === 2"
                  class="column"
                >
                  <h2 class="title column is-full">
                    Retour
                  </h2>
                  <b-datepicker
                    v-model="form.toDate"
                    :placeholder="'Date de fin'"
                    icon="calendar-today"
                    class="column is-full"
                  />
                  <div
                    v-for="(day,index) in nbOfDaysToPlan"
                    :key="index"
                    class="columns"
                  >
                    <div
                      v-if="nbOfDaysToPlan>1"
                      class="column is-2 dayNameColumn"
                    >
                      <a class="button is-secondary is-2">{{ days[index] }}</a>
                    </div>
                    <b-timepicker
                      v-model="form['return'+daysShort[index]+'Time']"
                      placeholder="heure de retour..."
                      class="column"
                    >
                      <button
                        class="button is-primary"
                        @click="form['return'+daysShort[index]+'Time'] = new Date()"
                      >
                        <b-icon icon="clock" />
                        <span>Maintenant</span>
                      </button>
                      <button
                        class="button is-tertiary"
                        @click="form['return'+daysShort[index]+'Time'] = null"
                      >
                        <b-icon icon="close" />
                        <span>Effacer</span>
                      </button>
                    </b-timepicker>
                    <!-- MARGIN -->
                    <b-select
                      v-model="form['return'+daysShort[index]+'Margin']"
                      class="column is-4"
                      placeholder="Marge"
                    >
                      <option
                        v-for="(margin,key) in marginsMn"
                        :key="key"
                        :value="margin"
                      >
                        {{ (1 > margin/60 > 0) ? margin : `${Math.trunc(margin/60)}H${margin%60}` }}
                      </option>
                    </b-select>
                  </div>
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
import moment from 'moment'
import GeoComplete from "./GeoComplete";
import BSelect from "buefy/src/components/select/Select";
export default {
  components: {
    BSelect,
    GeoComplete
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
    },
    sentToken: {
      type: String,
      default: ""
    },
    sentHydra: {
      type: String,
      default: ""
    },
    sentCommunity: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      origin: null,
      outward: this.sentOutward,
      communities: JSON.parse(this.sentHydra),
      idCommunity: this.sentCommunity,
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
      daysEn: [
        "Sunday",
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday"
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
        createToken: this.sentToken,
        origin: "",
        originStreetAddress : null,
        originPostalCode: null,
        originAddressLocality: null,
        originAddressCountry: null,
        originLatitude: null,
        originLongitude: null,
        destinationLatitude: null,
        destinationLongitude: null,
        destination: "",
        destinationStreetAddress: null,
        destinationPostalCode: null,
        destinationAddressLocality: null,
        destinationAddressCountry: null,
        role: this.sentRole,
        type: this.sentType,
        frequency: this.sentFrequency,
        outwardDate: null,
        outwardMargin: null,
        outwardTime: null,
        returnDate: null,
        returnMargin: null,
        returnTime: null,
        // Regular
        fromDate: null,
        toDate: null,
        returnMonTime: null,
        returnMonMargin: null,
        returnTueTime: null,
        returnTueMargin: null,
        returnThuTime: null,
        returnThuMargin: null,
        returnWedTime: null,
        returnWedMargin: null,
        returnFriTime: null,
        returnFriMargin: null,
        returnSatTime: null,
        returnSatMargin: null,
        returnSunTime: null,
        returnSunMargin: null,
        outwardMonTime: null,
        outwardMonMargin: null,
        outwardTueTime: null,
        outwardTueMargin: null,
        outwardThuTime: null,
        outwardThuMargin: null,
        outwardWedTime: null,
        outwardWedMargin: null,
        outwardFriTime: null,
        outwardFriMargin: null,
        outwardSatTime: null,
        outwardSatMargin: null,
        outwardSunTime: null,
        outwardSunMargin: null,
        community: this.sentCommunity || null
      }
    };
  },
  computed: {
    daysShort() {
      return this.daysEn.map(day => day.substring(0, 3));
    },
    nbOfDaysToPlan(){
      if(this.form.frequency === 2) return this.days.length;
      return 1;
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

      this.form[name + "StreetAddress"] = val.streetAddress;
      this.form[name + "PostalCode"] = val.postalCode;

      this.form[name + "AddressCountry"] = val.addressCountry;
      this.form[name + "AddressLocality"] = val.addressLocality;
    },
    /**
     * Send the form to the route /covoiturage/annonce/poster
     */
    onComplete() { 
      let adForm = new FormData();
      for (let prop in this.form) {
        let value = this.form[prop];
        if(!value) continue; // Value is empty, just skip it!
        // Convert date to required format
        if(prop.toLowerCase().includes('date')){
          value = moment(value).format('YYYY/MM/DD');
        }
        // Convert time to required format
        if(prop.toLowerCase().includes('time')){
          value = moment(value).format('HH:mm');
        }
        // Convert margin from min to sec
        if(prop.toLowerCase().includes('margin')){
          value *= 60;
        }
        // rename prop to be usable in the controller
        let renamedProp = prop === "createToken" ? prop : `ad_form[${prop}]`;
        adForm.append(renamedProp, value);
      }
      //  We post the form 🚀
      axios
        .post("/covoiturage/annonce/poster", adForm, {
          headers: {
            "Content-Type": "multipart/form-data"
          }
        })
      // ajouter ici un .then pour enregistrer une annonce avec une communauté
        .then(function(response) {
          window.location.href = '/covoiturage/annonce/'+response.data.proposal+'/resultats';
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
.dayNameColumn {
  text-align: left;
  a {
    width: 100%;
  }
}
</style>
