<template>
  <v-content>
    <v-container
      grid-list-md
      text-xs-center
      fluid
    >
      <!-- Title and subtitle -->
      <v-layout justify-center>
        <v-flex
          xs6
          text-center
        >
          <h1>{{ $t('title') }}</h1>
          <h3 v-if="step==1">
            {{ $t('subtitle') }}
          </h3>
          <!-- todo : remove this awful trick !! -->
          <h3 v-else>
            &nbsp;
          </h3>
        </v-flex>
      </v-layout>

      <!-- Stepper -->
      <v-layout justify-center>
        <v-flex
          xs6
          justify-center
        >
          <v-stepper
            v-model="step"
            alt-labels
          >
            <!-- Stepper Header -->
            <v-stepper-header
              v-show="step!==1"
            >
              <!-- Step 1 : search journey -->
              <v-stepper-step
                editable
                step="1"
                color="success"
              >
                {{ $t('stepper.header.search_journey') }}
              </v-stepper-step>
              <v-divider />

              <!-- Step 2 : planification -->
              <v-stepper-step
                editable
                step="2"
                color="success"
              >
                {{ $t('stepper.header.planification') }}
              </v-stepper-step>
              <v-divider />

              <!-- Step 3 : map -->
              <v-stepper-step
                editable
                step="3"
                color="success"
              >
                {{ $t('stepper.header.map') }}
              </v-stepper-step>
              <v-divider />

              <!-- Step 4 : passengers (if driver) -->
              <v-stepper-step
                editable
                step="4"
                color="success"
              >
                {{ $t('stepper.header.passengers') }}
              </v-stepper-step>
              <v-divider />

              <!-- Step 5 : participation (if driver) -->
              <v-stepper-step
                editable
                step="5"
                color="success"
              >
                {{ $t('stepper.header.participation') }}
              </v-stepper-step>
              <v-divider />

              <!-- Step 6 : message -->
              <v-stepper-step
                editable
                step="6"
                color="success"
              >
                {{ $t('stepper.header.message') }}
              </v-stepper-step>
              <v-divider />

              <!-- Step 7 : summary -->
              <v-stepper-step
                color="success"
                editable
                step="7"
              >
                {{ $t('stepper.header.summary') }}
              </v-stepper-step>
            </v-stepper-header>

            <!-- Stepper Content -->
            <v-stepper-items>
              <!-- Step 1 : search journey -->
              <v-stepper-content step="1">
                <search-journey
                  display-roles
                  :geo-search-url="geoSearchUrl"
                  :user="user"
                  :date="outwardDate"
                  @change="searchChanged"
                />
              </v-stepper-content>

              <!-- Step 2 : planification -->
              <v-stepper-content step="2">
                <ad-planification
                  :outward-date="outwardDate"
                  @change="planificationChanged"
                />
              </v-stepper-content>

              <!-- Step 3 : map -->
              <v-stepper-content step="3">
                <ad-route 
                  :geo-search-url="geoSearchUrl"
                  :user="user"
                />
              </v-stepper-content>

              <!-- Step 4 : passengers (if driver) -->
              <v-stepper-content
                step="4"
              >
                <v-layout
                  row
                  align-center
                  justify-center
                  mt-2
                >
                  <v-flex
                    xs8
                  >
                    <v-layout
                      row
                      wrap
                    >
                      {{ $t('stepper.content.passengers.seats.question') }}
                      <v-select
                        style="width:15px"
                        :label="$t('stepper.content.passengers.seats.label')"
                        :items="[1,2,3,4]"
                      />
                      {{ $t('stepper.content.passengers.seats.passengers') }}
                    </v-layout>

                    <v-layout>
                      {{ $t('stepper.content.passengers.luggage') }}
                      <v-spacer />
                      <v-switch
                        class="ma-2"
                      />
                    </v-layout>
                    <v-layout>
                      {{ $t('stepper.content.passengers.bike') }}
                      <v-spacer />
                      <v-switch
                        class="ma-2"
                      />
                    </v-layout>
                    <v-layout>
                      {{ $t('stepper.content.passengers.back_seats') }}
                      <v-spacer />
                      <v-switch
                        d-inline
                        class="ma-2"
                      />
                    </v-layout>
                  </v-flex>
                </v-layout>
              </v-stepper-content>

              <!-- Step 5 : participation (if driver) -->
              <v-stepper-content
                step="5"
              >
                <v-layout
                  wrap
                  row
                  align-center
                  justify-center
                >
                  {{ $t('stepper.content.participation.price') }}
                  <p>
                    <v-text-field type="number" />
                    <!-- TODO get the .env variable that defines the carpool price value -->
                    0.06€/km
                  </p>
                  {{ $t('stepper.content.participation.passengers') }}
                </v-layout>
              </v-stepper-content>

              <!-- Step 6 : message -->
              <v-stepper-content
                step="6"
              >
                <v-layout
                  wrap
                  row
                  align-center
                  justify-center
                >
                  <v-flex xs8>
                    <div class="text-center">
                      <v-textarea
                        name="input-7-1"
                        :label="$t('stepper.content.message.label')"
                        value=""
                        :placeholder="$t('stepper.content.message.placeholder')"
                      />
                    </div>
                  </v-flex>
                </v-layout>
              </v-stepper-content>

              <!-- Step 7 : summary -->
              <v-stepper-content
                step="7"
              >
                <ad-summary />
              </v-stepper-content>
            </v-stepper-items>
          </v-stepper>
        </v-flex>
      </v-layout>
      <!-- </v-stepper-content> -->

      <!-- Buttons Previous and Next step -->
      <v-layout
        mt-5
        justify-center
      >
        <v-btn
          v-if="step > 1"
          rounded
          color="primary" 
          align-center
          @click="--step"
        >
          {{ $t('stepper.buttons.previous') }}
        </v-btn>

        <v-btn
          v-if="step < 7"
          rounded
          color="primary"
          align-center
          style="margin-left: 30px"
          @click="step++"
        >
          {{ $t('stepper.buttons.next') }}
        </v-btn>
        <v-btn
          v-if="step === 7"
          rounded
          color="primary"
          style="margin-left: 30px"
          align-center
        >
          {{ $t('stepper.buttons.publish_ad') }}
        </v-btn>
      </v-layout>
    </v-container>
  </v-content>
</template>

<script>
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/AdPublish.json";
import TranslationsClient from "@clientTranslations/components/carpool/AdPublish.json";

import axios from "axios";
import moment from 'moment'
import SearchJourney from "@components/carpool/SearchJourney";
import AdPlanification from "@components/carpool/AdPlanification";
import AdRoute from "@components/carpool/AdRoute";
import AdSummary from "@components/carpool/AdSummary";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
    SearchJourney,
    AdPlanification,
    AdRoute,
    AdSummary
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: ""
    },
    user: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      outwardDate: {
        type: String,
        default: null
      },
      step: {
        type: Number,
        default: 1
      },
      step1: {
        type: Object,
        default: null
      },
      step2: {
        type: Object,
        default: null
      },
      step3: {
        type: Object,
        default: null
      },
      step4: {
        type: Object,
        default: null
      },
      step5: {
        type: Object,
        default: null
      },
      step6: {
        type: Object,
        default: null
      },
      step7: {
        type: Object,
        default: null
      },
      /*step: 1,
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
      timeStart: new Date(),
      timeReturn: new Date(),
      search: {
        type: Object,
        default: null
      }*/
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
    searchChanged: function(search) {
      if (search.date != this.outwardDate) {
        this.outwardDate = search.date;
      }
      this.step1 = search;
    },
    planificationChanged(planification) {
      if (planification.outwardDate != this.outwardDate) {
        this.outwardDate = planification.outwardDate;
      }
      this.step2 = planification;
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
        .then(function(response) {
          if(response.data.proposal !== 'undefined' ) {
            window.location.href = '/covoiturage/annonce/' + response.data.proposal + '/resultats';
          }
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

    .v-stepper{
        height: 600px;
    }

</style>
