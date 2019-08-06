<template>
  <section
    id="adCreateForm"
    class="section"
  >
    <v-container
      grid-list-md
      text-xs-center
    >
      <v-layout
        row
        wrap
      >
        <v-flex xs2 />
        <v-flex xs8>
          <h1>Publier une annonce Test</h1>
          <h5>
            <h3>Commencer votre annonce</h3>
            <v-stepper
              v-model="step"
              alt-labels
            >
              <v-stepper-header>
                <v-stepper-step
                  editable
                  step="1"
                  color="success"
                >
                  Commencer votre annonce
                </v-stepper-step>
                <v-divider />

                <v-stepper-step
                  editable
                  step="2"
                  color="success"
                >
                  Planification
                </v-stepper-step>

                <v-divider />
                <!-- Travel -->
                <v-stepper-step
                  editable
                  step="3"
                  color="success"
                >
                  Trajet
                </v-stepper-step>

                <v-divider />

                <v-stepper-step
                  editable
                  step="4"
                  color="success"
                >
                  Passagers
                </v-stepper-step>
                <v-divider />

                <v-stepper-step
                  editable
                  step="5"
                  color="success"
                >
                  Participation
                </v-stepper-step>
                <v-divider />

                <v-stepper-step
                  editable
                  step="6"
                  color="success"
                >
                  Message
                </v-stepper-step>
                <v-divider />

                <v-stepper-step
                  color="success"
                  editable
                  step="7"
                >
                  Récapitulatif
                </v-stepper-step>
              </v-stepper-header>

              <!-- Planification -->
              <v-stepper-items
                style="height: 500px"
              >
                <v-stepper-content step="1">
                  <v-container
                    grid-list-md
                    text-xs-center
                  >
                    <v-layout
                      row
                      wrap
                    >
                      <v-flex
                        xs2
                      />
                      <v-flex
                        xs6
                      >
                        <v-menu
                          v-model="menu2"
                          :close-on-content-click="false"
                          :nudge-right="40"
                          lazy
                          transition="scale-transition"
                          offset-y
                          full-width
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on }">
                            <v-text-field
                              v-model="date1"
                              label="Date de départ"
                              prepend-icon=""
                              readonly
                              v-on="on"
                            />
                          </template>
                          <v-date-picker
                            v-model="date1"
                            @input="menu2 = false"
                          />
                        </v-menu>
                      </v-flex>
                      <v-flex
                        xs4
                      >
                        <v-menu
                          ref="menu"
                          v-model="menu3"
                          :close-on-content-click="false"
                          :nudge-right="40"
                          :return-value.sync="time1"
                          lazy
                          transition="scale-transition"
                          offset-y
                          full-width
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on }">
                            <v-text-field
                              v-model="time1"
                              label="Heure de départ"
                              prepend-icon=""
                              readonly
                              v-on="on"
                            />
                          </template>
                          <v-time-picker
                            v-if="menu3"
                            v-model="time1"
                            format="24hr"
                            @click:minute="$refs.menu.save(time1)"
                          />
                        </v-menu>
                      </v-flex>
                    </v-layout>
                    <v-layout
                      row
                      wrap
                    >
                      <v-flex
                        xs2
                      >
                        <v-checkbox
                          label="retour"
                          color="success"
                          value="success"
                          hide-details
                        />
                      </v-flex>
                      <v-flex
                        xs6
                      >
                        <v-menu
                          v-model="menu4"
                          :close-on-content-click="false"
                          :nudge-right="40"
                          lazy
                          transition="scale-transition"
                          offset-y
                          full-width
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on }">
                            <v-text-field
                              v-model="date2"
                              label="Date de retour"
                              prepend-icon=""
                              readonly
                              v-on="on"
                            />
                          </template>
                          <v-date-picker
                            v-model="date2"
                            @input="menu4 = false"
                          />
                        </v-menu>
                      </v-flex>
                      <v-flex
                        xs4
                      >
                        <v-menu
                          ref="menu"
                          v-model="menu5"
                          :close-on-content-click="false"
                          :nudge-right="40"
                          :return-value.sync="time2"
                          lazy
                          transition="scale-transition"
                          offset-y
                          full-width
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on }">
                            <v-text-field
                              v-model="time2"
                              label="Heure de retour"
                              prepend-icon=""
                              readonly
                              v-on="on"
                            />
                          </template>
                          <v-time-picker
                            v-if="menu5"
                            v-model="time2"
                            format="24hr"
                            @click:minute="$refs.menu.save(time2)"
                          />
                        </v-menu>
                        {{ communities.value }}
                        <v-select
                          v-if="!idCommunity"
                          v-model="form.community"
                          name="selectCommunity"
                          :native-value="4"
                          type="is-primary"
                          :items="communities"
                          :item-text="Object.values(communities)"
                          :item-value="Object.keys(communities)"
                        />
                        <p
                          v-else
                        >
                          {{ communities[idCommunity] }}
                        </p>
                      </v-flex>
                    </v-layout>
                  </v-container>
                </v-stepper-content>

                <v-stepper-content step="2">
                  <v-container
                    grid-list-md
                    text-xs-center
                  >
                    <v-layout
                      row
                      wrap
                    >
                      <v-flex
                        xs2
                      />
                      <v-flex
                        xs6
                      >
                        <v-menu
                          v-model="menu2"
                          :close-on-content-click="false"
                          :nudge-right="40"
                          lazy
                          transition="scale-transition"
                          offset-y
                          full-width
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on }">
                            <v-text-field
                              v-model="date1"
                              label="Date de départ"
                              prepend-icon=""
                              readonly
                              v-on="on"
                            />
                          </template>
                          <v-date-picker
                            v-model="date1"
                            @input="menu2 = false"
                          />
                        </v-menu>
                      </v-flex>
                      <v-flex
                        xs4
                      >
                        <v-menu
                          ref="menu"
                          v-model="menu3"
                          :close-on-content-click="false"
                          :nudge-right="40"
                          :return-value.sync="time1"
                          lazy
                          transition="scale-transition"
                          offset-y
                          full-width
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on }">
                            <v-text-field
                              v-model="time1"
                              label="Heure de départ"
                              prepend-icon=""
                              readonly
                              v-on="on"
                            />
                          </template>
                          <v-time-picker
                            v-if="menu3"
                            v-model="time1"
                            format="24hr"
                            @click:minute="$refs.menu.save(time1)"
                          />
                        </v-menu>
                      </v-flex>
                    </v-layout>
                    <v-layout
                      row
                      wrap
                    >
                      <v-flex
                        xs2
                      >
                        <v-checkbox
                          label="retour"
                          color="success"
                          value="success"
                          hide-details
                        />
                      </v-flex>
                      <v-flex
                        xs6
                      >
                        <v-menu
                          v-model="menu4"
                          :close-on-content-click="false"
                          :nudge-right="40"
                          lazy
                          transition="scale-transition"
                          offset-y
                          full-width
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on }">
                            <v-text-field
                              v-model="date2"
                              label="Date de retour"
                              prepend-icon=""
                              readonly
                              v-on="on"
                            />
                          </template>
                          <v-date-picker
                            v-model="date2"
                            @input="menu4 = false"
                          />
                        </v-menu>
                      </v-flex>
                      <v-flex
                        xs4
                      >
                        <v-menu
                          ref="menu"
                          v-model="menu5"
                          :close-on-content-click="false"
                          :nudge-right="40"
                          :return-value.sync="time2"
                          lazy
                          transition="scale-transition"
                          offset-y
                          full-width
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on }">
                            <v-text-field
                              v-model="time2"
                              label="Heure de retour"
                              prepend-icon=""
                              readonly
                              v-on="on"
                            />
                          </template>
                          <v-time-picker
                            v-if="menu5"
                            v-model="time2"
                            format="24hr"
                            @click:minute="$refs.menu.save(time2)"
                          />
                        </v-menu>
                      </v-flex>
                    </v-layout>
                  </v-container>
                </v-stepper-content>

                <v-stepper-content step="3">
                  <v-container
                    grid-list-md
                    text-xs-center
                  >
                    <v-layout
                      row
                      wrap
                    >
                      <v-flex
                        xs12
                      />
                      <v-flex
                        xs6
                      >
                        <GeoComplete
                          name="origin"
                          placeholder="Depuis"
                          :url="geoSearchUrl"
                          @geoSelected="selectedGeo"
                        />
                      </v-flex>
                      <v-flex
                        xs4
                      />
                    </v-layout>
                    <v-layout
                      row
                      wrap
                    >
                      <v-flex
                        xs12
                      />
                      <v-flex
                        xs6
                      >
                        <GeoComplete
                          placeholder="Vers"
                          :url="geoSearchUrl"
                          name="destination"
                          @geoSelected="selectedGeo"
                        />
                      </v-flex>
                      <v-flex
                        xs4
                      />
                    </v-layout>
                  </v-container>
                </v-stepper-content>

                <!-- Passenger(s) -->
                <v-stepper-content
                  step="4"
                >
                  <v-layout
                    row
                  >
                    <v-flex
                      xs12
                      offset-sm3
                    >
                      <v-layout>
                        J'ai de la place pour :
                        <v-select
                          style="width:15px"
                          label="Place(s)"
                          :items="[1,2,3,4]"
                        />
                        passager(s)
                      </v-layout>
                      <v-layout>
                        J'ai de la place pour des gros bagages
                        <v-switch
                          class="ma-2"
                        />
                      </v-layout>
                      <v-layout>
                        Je peux tranporter un vélo
                        <v-switch
                          class="ma-2"
                        />
                      </v-layout>
                      <v-layout>
                        Maximum 2 personnes à l'arrière
                        <v-switch
                          d-inline
                          class="ma-2"
                        />
                      </v-layout>
                    </v-flex>
                  </v-layout>
                </v-stepper-content>

                <!-- Price Carpool -->
                <v-stepper-content
                  step="5"
                >
                  <v-layout
                    wrap
                    row
                    align-center
                  >
                    Participation
                    <p>
                      <v-text-field type="number" />
                      <!-- TODO get the .env variable that defines the carpool price value -->
                      0.06€/km
                    </p>
                    passager(s)
                  </v-layout>
                </v-stepper-content>

                <!-- Message -->
                <v-stepper-content
                  step="6"
                >
                  <v-layout
                    wrap
                    row
                  >
                    <v-flex xs8>
                      <div class="text-center">
                        <v-textarea
                          name="input-7-1"
                          label="Mon message aux passagers"
                          value=""
                          placeholder="Laissez un petit message ..."
                        />
                      </div>
                    </v-flex>
                  </v-layout>
                </v-stepper-content>

                <!-- summary / recap -->
                <v-stepper-content
                  step="7"
                >
                  <v-container
                    fluid
                    class="pa-0 text-center"
                  >
                    <v-layout
                      wrap
                      align-center
                    >
                      <v-flex
                        xs12
                        sm12
                      >
                        <div class="text-center">
                          <div class="my-2">
                            <v-btn
                              small
                              color="primary"
                              dark
                            >
                              Small Button
                            </v-btn>
                          </div>
                          <div>TODO make the summary</div>
                          <div class="my-2">
                            <v-btn
                              color="warning"
                              dark
                            >
                              Normal Button
                            </v-btn>
                          </div>
                          <div>
                            Rappel Trajet
                          </div>
                          <div class="my-2">
                            <v-btn
                              color="error"
                              dark
                              large
                            >
                              Large Button
                            </v-btn>
                          </div>
                          <div>
                            Mon message aux passagers
                            <v-textarea
                              class="my-2"
                              name="input-7-1"
                              label="Mon message aux passagers"
                              value=""
                              placeholder="Laissez un petit message ..."
                            />
                          </div>
                          <div class="my-2">
                            Carte du trajet
                          </div>
                        </div>
                      </v-flex>
                    </v-layout>
                  </v-container>
                </v-stepper-content>
              </v-stepper-items>
            </v-stepper>
          </h5>
        </v-flex>
      </v-layout>

      <!-- </v-stepper-content> -->

      <!-- je teste pour voir ce que ça raconte si on fait propre -->
      <v-layout justify-center>
        <v-btn
          v-if="step > 1"
          rounded
          color="primary"
          align-center
          @click="--step"
        >
          Précédent
        </v-btn>

        <v-btn
          v-if="step < 7"
          rounded
          color="primary"
          align-center
          style="margin-left: 30px"
          @click="step++"
        >
          Suivant
        </v-btn>
        <v-btn
          v-if="step === 7"
          rounded
          color="primary"
          style="margin-left: 30px"
          align-center
        >
          Publier mon annonce
        </v-btn>
      </v-layout>
      <!-- </v-flex> -->
      <v-flex xs2 />
      <!-- </v-layout> -->
    </v-container>
  </section>
</template>

<script>
import axios from "axios";
import moment from 'moment'
import GeoComplete from "./GeoComplete";
export default {
  components: {
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
      //add data from vuetify test before merging all
      step: 1,
      date1: new Date().toISOString().substr(0, 10),
      time1: null,
      date2: new Date().toISOString().substr(0, 10),
      time2: null,
      menu2: false,
      menu3: false,
      menu4: false,
      menu5: false,
      numberP: [1,2,3,4,5],
      /////////////////////////////////////
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

   .layout .align-center {
     padding:12px !important;
     color: blueviolet;
   }

  .v-stepper__items{
    height: 800px;
  }

</style>
