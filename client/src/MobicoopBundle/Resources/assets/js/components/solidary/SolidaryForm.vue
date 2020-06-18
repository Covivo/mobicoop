<template>
  <v-container>
    <v-row
      justify="center"
      align="center"
    >
      <v-col
        cols="12"
        align="center"
      >
        <!-- STEPPER -->
        <v-stepper
          v-model="step"
          class="elevation-0"
        >
          <!-- STEPPER HEADER -->
          <v-stepper-header
            class="elevation-0"
          >
            <!-- STEPPER HEADER 1 : ORIGIN -->
            <v-stepper-step
              :step="1"
              :complete="step1Valid()"
            >
              {{ $t('stepper.origin') }}
            </v-stepper-step>

            <v-divider />

            <!-- STEP 2 : STRUCTURE / PROOFS -->
            <v-stepper-step
              :step="2"
              :complete="step2Valid()"
            >
              {{ $t('stepper.service') }}
            </v-stepper-step>

            <v-divider />

            <!-- STEP 3 : SUBJECT / DESTINATION / FREQUENCY / NEEDS -->
            <v-stepper-step
              :step="3"
              :complete="step3Valid()"
            >
              {{ $t('stepper.yourJourney') }}
            </v-stepper-step>

            <v-divider />

            <!-- STEP 4 : PUNCTUAL / REGULAR -->
            <v-stepper-step 
              :step="4"
              :complete="step4Valid()"
            >
              {{ form.regular ? $t('stepper.regular') : $t('stepper.ponctual') }}
            </v-stepper-step>

            <v-divider />

            <!-- STEP 5 : USER -->
            <v-stepper-step 
              :step="5"
              :complete="step5Valid()"
            >
              {{ $t('stepper.you') }}
            </v-stepper-step>

            <v-divider />

            <!-- STEP 6 : SUMMARY -->
            <v-stepper-step
              :step="6"
            >
              {{ $t('stepper.summary') }}
            </v-stepper-step>
          </v-stepper-header>


          <!-- STEPPER ITEMS -->
          <v-stepper-items>
            <!-- STEP 1 : ORIGIN -->
            <v-stepper-content step="1">
              <v-form
                ref="step 1"
                class="pb-2"
                @submit.prevent
              >
                <v-card
                  class="mb-12"
                  flat
                >
                  <v-row
                    justify="center"
                  >
                    <v-col
                      cols="8"
                    >
                      <!--GeoComplete -->
                      <GeoComplete
                        :url="geoSearchUrl"
                        :label="$t('origin.placeholder')"
                        :token="user ? user.geoToken : ''"
                        :display-name-in-selected="false"
                        required
                        @address-selected="originSelected"
                      />
                    </v-col>
                  </v-row>
                </v-card>
                
                <!-- Next Button -->
                <v-btn
                  ref="button"
                  rounded
                  class="my-13 mr-12 mt-12 "
                  color="secondary"
                  width="150px"
                  :disabled="!step1Valid()"
                  @click="nextStep(1)"
                >
                  {{ $t('ui.button.next') }}
                </v-btn>
              </v-form>
            </v-stepper-content>

            <!-- STEP 2 : STRUCTURE / PROOFS -->
            <v-stepper-content step="2">
              <v-form
                ref="step 2"
                class="pb-2"
                @submit.prevent
              >
                <v-card
                  class="mb-12"
                  flat
                >
                  <v-row
                    justify="center"
                  >
                    <!-- Structure -->
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('structure.title') }}</span> 
                      </p>
                      <v-select
                        v-model="form.structure"
                        :items="structures"
                        item-text="name"
                        item-value="id"
                        :label="$t('structure.placeholder')"
                      />
                      {{ $t('structure.text') }}
                    </v-col>

                    <!-- Mandatory Proofs -->
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('structure.mandatoryProofs') }}</span> 
                      </p>
                    </v-col>

                    <!-- Optional Proofs -->
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('structure.optionalProofs') }}</span> 
                      </p>
                    </v-col>
                  </v-row>
                </v-card>

                <!-- Previous Button -->
                <v-btn
                  ref="button"
                  rounded
                  class="my-13 mr-12 mt-12 "
                  color="secondary"
                  width="150px"
                  @click="--step"
                >
                  {{ $t('ui.button.previous') }}
                </v-btn>

                <!-- Next Button -->
                <v-btn
                  ref="button"
                  rounded
                  class="mr-4 mb-100 mt-12"
                  color="secondary"
                  width="150px"
                  :disabled="!step2Valid()"
                  @click="nextStep(2)"
                >
                  {{ $t('ui.button.next') }}
                </v-btn>
              </v-form>
            </v-stepper-content>

            <!-- STEP 3 : SUBJECT / DESTINATION / FREQUENCY / NEEDS -->
            <v-stepper-content step="3">
              <v-form
                ref="step 3"
                class="pb-2"
                @submit.prevent
              >
                <v-card
                  class="mb-12"
                  flat
                >
                  <v-row
                    justify="center"
                  >
                    <!-- Subject -->
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('yourJourney.subjectTitle') }}</span> 
                      </p>
                      <v-select
                        v-model="form.subject"
                        :items="subjects"
                        item-text="name"
                        item-value="id"
                        :label="$t('yourJourney.subject')"
                      />
                    </v-col>

                    <!-- Destination -->
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('yourJourney.destinationTitle') }}</span> 
                      </p>
                      <!--GeoComplete -->
                      <GeoComplete
                        :url="geoSearchUrl"
                        :label="$t('yourJourney.destination')"
                        :token="user ? user.geoToken : ''"
                        :display-name-in-selected="false"
                        @address-selected="destinationSelected"
                      />                  
                    </v-col>

                    <!-- Frequency -->
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('yourJourney.regular.question') }}</span> 
                      </p>
                      <v-radio-group
                        v-model="form.regular"
                        :mandatory="false"
                      >
                        <v-radio
                          :label="$t('yourJourney.regular.no')"
                          :value="false"
                        />
                        <v-radio
                          :label="$t('yourJourney.regular.yes')"
                          :value="true"
                        />
                      </v-radio-group>                                 
                    </v-col>

                    <!-- Needs -->
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('yourJourney.otherInfoTitle') }}</span> 
                      </p>
                      <v-combobox
                        v-model="form.needs"
                        :items="needs"
                        :label="$t('yourJourney.otherInfo')"
                        multiple
                        chips
                      />
                    </v-col>
                  </v-row>
                </v-card>

                <!-- Previous Button -->
                <v-btn
                  ref="button"
                  rounded
                  class="my-13 mr-12 mt-12 "
                  color="secondary"
                  width="150px"
                  @click="--step"
                >
                  {{ $t('ui.button.previous') }}
                </v-btn>

                <!-- Next Button -->
                <v-btn
                  ref="button"
                  rounded
                  class="mr-4 mb-100 mt-12"
                  color="secondary"
                  width="150px"
                  :disabled="!step3Valid()"
                  @click="nextStep(3)"
                >
                  {{ $t('ui.button.next') }}
                </v-btn>
              </v-form>
            </v-stepper-content>

            <!-- STEP 4-1 : PUNCTUAL -->
            <v-stepper-content 
              v-if="!form.regular"
              step="4"
            >
              <v-form
                ref="step 4"
                class="pb-2"
                @submit.prevent
              >
                <v-card
                  class="mb-12"
                  flat
                >
                  <v-row
                    justify="center"
                  >
                    <!-- Start date -->
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('frequency.punctual.startDateTitle') }}</span> 
                      </p>
                      <v-radio-group
                        v-model="punctualStartDateChoice"
                      >
                        <v-row
                          align="center"
                          no-gutters
                        >
                          <v-radio
                            :value="0"
                            hide-details
                          />
                          <v-menu
                            v-model="menuPunctualStartDate"
                            :close-on-content-click="false"
                            transition="scale-transition"
                            offset-y
                            min-width="290px"
                          >
                            <template v-slot:activator="{ on }">
                              <v-text-field
                                :value="computedPunctualStartDateFormat"
                                :label="$t('frequency.punctual.startDateChoice1')"
                                readonly
                                clearable
                                :disabled="punctualStartDateChoice != 0"
                                v-on="on"
                                @click:clear="clearPunctualStartDate"
                              />
                            </template>
                            <v-date-picker
                              v-model="punctualStartDate"
                              :locale="locale"
                              no-title
                              :min="nowDate"
                              first-day-of-week="1"
                              @input="menuPunctualStartDate = false"
                              @change="changePunctualStartDate()"
                            />
                          </v-menu>
                        </v-row>
                        <v-radio
                          :label="$t('frequency.punctual.startDateChoice2')"
                          :value="1"
                        />
                        <v-radio
                          :label="$t('frequency.punctual.startDateChoice3')"
                          :value="2"
                        />
                        <v-radio
                          :label="$t('frequency.punctual.startDateChoice4')"
                          :value="3"
                        />
                      </v-radio-group>                                 
                    </v-col>

                    <!-- Start time -->
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('frequency.startTimeTitle') }}</span> 
                      </p>
                      <v-radio-group
                        v-model="startTimeChoice"
                      >
                        <v-row
                          align="center"
                          no-gutters
                        >
                          <v-radio
                            :value="0"
                            hide-details
                          />
                          <v-menu
                            v-model="menuStartTime"
                            :close-on-content-click="false"
                            transition="scale-transition"
                            offset-y
                            min-width="290px"
                          >
                            <template v-slot:activator="{ on }">
                              <v-text-field
                                v-model="startTime"
                                :label="$t('frequency.startTimeChoice1')"
                                prepend-icon=""
                                readonly
                                clearable
                                :disabled="startTimeChoice != 0"
                                v-on="on"
                                @click:clear="clearStartTime"
                              />
                            </template>
                            <v-time-picker
                              v-model="startTime"
                              format="24hr"
                              header-color="secondary"
                              @click:minute="menuStartTime = false"
                              @change="changeStartTime()"
                            />
                          </v-menu>
                        </v-row>
                        <v-radio
                          :label="$t('frequency.startTimeChoice2')"
                          :value="1"
                        />
                        <v-radio
                          :label="$t('frequency.startTimeChoice3')"
                          :value="2"
                        />
                        <v-radio
                          :label="$t('frequency.startTimeChoice4')"
                          :value="3"
                        />
                      </v-radio-group>                                 
                    </v-col>

                    <!-- End time -->
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('frequency.endTimeTitle') }}</span> 
                      </p>
                      <v-radio-group
                        v-model="endTimeChoice"
                        :disabled="this.form.startTime == null"
                      >
                        <v-radio
                          :label="$t('frequency.endTimeChoice1')"
                          :value="0"
                        />
                        <v-row
                          align="center"
                          no-gutters
                        >
                          <v-radio
                            :value="1"
                            hide-details
                          />
                          <v-menu
                            v-model="menuEndTime"
                            :close-on-content-click="false"
                            transition="scale-transition"
                            offset-y
                            min-width="290px"
                          >
                            <template v-slot:activator="{ on }">
                              <v-text-field
                                v-model="endTime"
                                :label="$t('frequency.endTimeChoice2')"
                                prepend-icon=""
                                readonly
                                clearable
                                :disabled="endTimeChoice != 0"
                                v-on="on"
                                @click:clear="clearEndTime"
                              />
                            </template>
                            <v-time-picker
                              v-model="endTime"
                              format="24hr"
                              header-color="secondary"
                              @click:minute="menuEndTime = false"
                              @change="changeEndTime()"
                            />
                          </v-menu>
                        </v-row>
                        <v-radio
                          :label="$t('frequency.endTimeChoice3')"
                          :value="2"
                        />
                        <v-radio
                          :label="$t('frequency.endTimeChoice4')"
                          :value="3"
                        />
                        <v-radio
                          :label="$t('frequency.endTimeChoice5')"
                          :value="4"
                        />
                      </v-radio-group>                                 
                    </v-col>
                  </v-row>
                </v-card>

                <!-- Previous Button -->
                <v-btn
                  ref="button"
                  rounded
                  class="my-13 mr-12 mt-12 "
                  color="secondary"
                  @click="--step"
                >
                  {{ $t('ui.button.previous') }}
                </v-btn>

                <!-- Next Button -->
                <v-btn
                  ref="button"
                  rounded
                  class="mr-4 mb-100 mt-12"
                  color="secondary"
                  width="150px"
                  :disabled="!step4Valid()"
                  @click="nextStep(5)"
                >
                  {{ $t('ui.button.next') }}
                </v-btn>
              </v-form>
            </v-stepper-content>

            <!-- STEP 4-2 : REGULAR -->
            <v-stepper-content 
              v-if="form.regular"
              step="4"
            >
              <v-form
                ref="step 4"
                class="pb-2"
                @submit.prevent
              >
                <v-card
                  class="mb-12"
                  flat
                >
                  <v-row
                    justify="center"
                  >
                    <!-- Days -->
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('frequency.regular.days') }}</span> 
                      </p>
                    </v-col>

                    <!-- Start time -->
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('frequency.startTimeTitle') }}</span> 
                      </p>
                      <v-radio-group
                        v-model="startTimeChoice"
                      >
                        <v-row
                          align="center"
                          no-gutters
                        >
                          <v-radio
                            :value="0"
                            hide-details
                          />
                          <v-text-field 
                            v-model="startTime"
                            :label="$t('frequency.startTimeChoice1')"
                            :disabled="startTimeChoice != 0"
                          />
                        </v-row>
                        <v-radio
                          :label="$t('frequency.startTimeChoice2')"
                          :value="1"
                        />
                        <v-radio
                          :label="$t('frequency.startTimeChoice3')"
                          :value="2"
                        />
                        <v-radio
                          :label="$t('frequency.startTimeChoice4')"
                          :value="3"
                        />
                      </v-radio-group>                                 
                    </v-col>

                    <!-- End time -->
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('frequency.endTimeTitle') }}</span> 
                      </p>
                      <v-radio-group
                        v-model="endTimeChoice"
                      >
                        <v-radio
                          :label="$t('frequency.endTimeChoice1')"
                          :value="0"
                        />
                        <v-row
                          align="center"
                          no-gutters
                        >
                          <v-radio
                            :value="1"
                            hide-details
                          />
                          <v-text-field 
                            v-model="endTime"
                            :label="$t('frequency.endTimeChoice2')"
                            :disabled="endTimeChoice != 1"
                          />
                        </v-row>
                        <v-radio
                          :label="$t('frequency.endTimeChoice3')"
                          :value="2"
                        />
                        <v-radio
                          :label="$t('frequency.endTimeChoice4')"
                          :value="3"
                        />
                        <v-radio
                          :label="$t('frequency.endTimeChoice5')"
                          :value="4"
                        />
                      </v-radio-group>                                 
                    </v-col>
                  </v-row>
                </v-card>

                <!-- Previous Button -->
                <v-btn
                  ref="button"
                  rounded
                  class="my-13 mr-12 mt-12 "
                  color="secondary"
                  @click="--step"
                >
                  {{ $t('ui.button.previous') }}
                </v-btn>

                <!-- Next Button -->
                <v-btn
                  ref="button"
                  rounded
                  class="mr-4 mb-100 mt-12"
                  color="secondary"
                  width="150px"
                  :disabled="!step4Valid()"
                  @click="nextStep(5)"
                >
                  {{ $t('ui.button.next') }}
                </v-btn>
              </v-form>
            </v-stepper-content>

            <!-- STEP 5 : USER -->
            <v-stepper-content step="5">
              <v-form
                ref="step 5"
                class="pb-2"
                @submit.prevent
              >
                <v-card
                  class="mb-12"
                  flat
                >
                  <v-row justify="center">
                    <v-col
                      cols="8"
                    >
                      <v-text-field
                        v-model="form.phoneNumber"
                        :label="$t('models.user.phone.placeholder') + ' *'"
                        :rules="rules.phoneNumberRules"
                        name="phone"
                      />
                    </v-col>
                    <v-col
                      cols="8"
                    >
                      <v-text-field
                        v-model="form.email"
                        :label="$t('models.user.email.placeholder') + ' *'"
                        :rules="rules.emailRules"
                        name="email"
                      />
                    </v-col>
                    <v-col
                      cols="8"
                    >
                      <v-text-field
                        v-model="form.givenName"
                        :label="$t('models.user.givenName.placeholder') + ' *'"
                        :rules="rules.givenNameRules"
                        persistent-hint
                        name="firstName"
                        :hint="$t('firstNameText')"
                      />
                    </v-col>
                    <v-col cols="8">
                      <v-text-field
                        v-model="form.familyName"
                        :label="$t('models.user.familyName.placeholder') + ' *'"
                        :rules="rules.familyNameRules"
                        name="lastName"
                      />
                    </v-col>
                    <v-col cols="8">
                      <v-menu
                        ref="menu"
                        v-model="pickerActive"
                        :close-on-content-click="false"
                        transition="scale-transition"
                        offset-y
                        min-width="290px"
                      >
                        <template v-slot:activator="{ on }">
                          <v-text-field
                            v-model="form.birthDate"
                            :label="$t('birthDate.placeholder')+` *`"
                            readonly
                            required
                            v-on="on"
                          />
                        </template>
                        <v-date-picker
                          ref="picker"
                          v-model="form.birthDate"
                          :locale="locale"
                          first-day-of-week="1"
                          @change="save"
                        />
                      </v-menu>
                    </v-col>
                  </v-row>
                </v-card>

                <!-- Previous Button -->
                <v-btn
                  ref="button"
                  rounded
                  class="my-13 mr-12 mt-12 "
                  color="secondary"
                  @click="--step"
                >
                  {{ $t('ui.button.previous') }}
                </v-btn>

                <!-- Next Button -->
                <v-btn
                  ref="button"
                  rounded
                  class="mr-4 mb-100 mt-12"
                  color="secondary"
                  width="150px"
                  :disabled="!step5Valid()"
                  @click="nextStep(6)"
                >
                  {{ $t('ui.button.next') }}
                </v-btn>
              </v-form>
            </v-stepper-content>

            <!-- STEP 6 : SUMMARY -->
            <v-stepper-content step="6">
              <v-form
                ref="step 6"
                class="pb-2"
                @submit.prevent
              >
                <v-card
                  class="mb-12"
                  flat
                />

                <!-- Previous Button -->
                <v-btn
                  ref="button"
                  rounded
                  class="my-13 mr-12 mt-12 "
                  color="secondary"
                  @click="--step"
                >
                  {{ $t('ui.button.previous') }}
                </v-btn>

                <!-- Submit Button -->
                <v-btn
                  color="secondary"
                  rounded
                  class="mr-4 mb-100 mt-12"
                  :loading="loading"
                  width="150px"
                >
                  {{ $t('ui.button.register') }}
                </v-btn>
              </v-form>
            </v-stepper-content>
          </v-stepper-items>
        </v-stepper>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import {merge, find} from "lodash";
import axios from "axios";
import moment from "moment";
import Translations from "@translations/components/solidary/SolidaryForm.js";
import TranslationsClient from "@clientTranslations/components/solidary/SolidaryForm.js";
import GeoComplete from "@js/components/utilities/GeoComplete";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  components: {
    GeoComplete
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
  data () {
    return {
      locale: this.$i18n.locale,

      // loading state for submit button
      loading: false,

      // form validation state
      valid: false,

      // alert message
      alert: {
        type: "success",
        show: false,
        message: ""
      },

      // picker state
      pickerActive: false,

      // structures list (dynamically called after origin)
      structures: [
        'CCAS Nancy',
        'Les petits amis du coin de la rue'
      ],
      subjects: [
        'Faire mes courses',
        'Aller à un RDV médical',
        'Aller à un RDV administratif',
        'Faire une sortie culturelle',
        'Autre motif'
      ],
      needs: [
        'J\'ai besoin d\'être accompagné jusqu\'à ma porte',
        'J\'ai besoin qu\'on monte mes courses',
        'J\'invite à prendre un café'
      ],

      // current step
      step: 1,

      // dates and times
      punctualStartDateChoice: null,  // punctual radio start date choice
      punctualStartDate: null,        // punctual start date picked 
      menuPunctualStartDate: false,   
      regularStartDateChoice: null,   // regular range start date choice
      regularEndDateChoice: null,     // regular range end date choice
      startTimeChoice: null,          // radio start time choice
      endTimeChoice: null,            // radio end time choice
      startTime: null,                // start time picked
      menuStartTime: false,
      endTime: null,                  // end time picked
      menuEndTime: false,

      nowDate : new Date().toISOString().slice(0,10),
      
      // form values
      form: {      
        origin: null,
        structure: null,
        proofs: null,
        comment: null,
        subject: null,
        destination: null,
        regular: null,
        needs: null,
        startDate: null,
        endDate: null,
        regularDays: null,
        startTime: null,
        endTime: null,
        marginDuration: 15,
        givenName: this.user && this.user.givenName ? this.user.givenName : "",
        familyName: this.user && this.user.familyName ? this.user.familyName : "",
        email: this.user && this.user.email ? this.user.email : "",
        phoneNumber: this.user && this.user.telephone ? this.user.telephone : null,
        birthDate: this.user && this.user.birthDate ? moment(this.user.birthDate.toString()).format("YYYY-MM-DD") : null
      },
      rules: {
        givenNameRules: [
          v => !!v || this.$t("models.user.givenName.errors.required"),
        ],
        familyNameRules: [
          v => !!v || this.$t("models.user.familyName.errors.required"),
        ],
        telephoneRules: [
          v => !!v || this.$t("models.user.phone.errors.required"),
          v => (/^((\+)33|0)[1-9](\d{2}){4}$/).test(v) || this.$t("models.user.phone.errors.valid")
        ],
        emailRules: [
          v => !!v || this.$t("models.user.email.errors.required"),
          v => /.+@.+/.test(v) || this.$t("models.user.email.errors.valid")
        ]
      },
      years: {
        max: moment().format(),
        min: moment().subtract(100, 'years').format()
      }
    }
  },
  computed: {
    computedPunctualStartDateFormat() {
      return this.punctualStartDate
        ? moment(this.punctualStartDate).format(this.$t("ui.i18n.date.format.fullDate"))
        : "";
    }
  },
  watch: {
    pickerActive(val) {
      val && this.$nextTick(() => (this.$refs.picker.activePicker = 'YEAR'))
    },
    punctualStartDateChoice(val) {
      if (val == 0) {
        this.form.startDate = null;
        this.form.endDate = null;
        if (this.punctualStartDate != null) {
          this.form.startDate = moment(this.punctualStartDate); 
        }
      } else if (val == 1) {
        this.form.startDate = moment();
        this.form.endDate = moment().add(7, 'days');
      } else if (val == 2) {
        this.form.startDate = moment();
        this.form.endDate = moment().add(14, 'days');
      } else if (val == 3) {
        this.form.startDate = moment();
        this.form.endDate = moment().add(1, 'months');
      }
    },
    startTimeChoice(val) {
      this.form.startTime = null;
      this.form.marginDuration = 15;  
      if (val == 0 && this.startTime != null) {
        this.form.startTime = this.startTime;
      } else if (val == 1) {
        this.form.startTime = "10:30";
        this.form.marginDuration = 150;
      } else if (val == 2) {
        this.form.startTime = "15:30";
        this.form.marginDuration = 150;
      } else if (val == 3) {
        this.form.startTime = "19:30";
        this.form.marginDuration = 90;
      }
    },
    endTimeChoice(val) {
      this.form.endTime = null;
      if (val == 1 && this.endTime != null) {
        this.form.endTime = this.endTime;
      } else if (val == 2) {
        this.form.endTime = moment(moment().format('YYYY-MM-DD')+' '+this.form.startTime).add(1, 'hours').format('HH:MM');
      } else if (val == 3) {
        this.form.endTime = moment(moment().format('YYYY-MM-DD')+' '+this.form.startTime).add(2, 'hours').format('HH:MM');
      } else if (val == 4) {
        this.form.endTime = moment(moment().format('YYYY-MM-DD')+' '+this.form.startTime).add(3, 'hours').format('HH:MM');
      }
    },
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    nextStep (n) {
      this.step += 1
    },
    previousStep (n) {
      this.step -= 1
    },
    step1Valid() {
      return this.form.origin != null
    },
    step2Valid() {
      return this.form.structure != null
    },
    step3Valid() {
      return this.form.subject != null && this.form.regular != null
    },
    step4Valid() {
      if (!this.form.regular) {
        return (
          (this.startTimeChoice > 0 || this.startTime != null) && 
          (this.endTimeChoice != 1 || this.endTime != null) && 
          (this.punctualStartDateChoice > 0 || this.punctualStartDate != null))
      }
      return (
        (this.startTimeChoice > 0 || this.startTime != null) && 
        (this.endTimeChoice != 1 || this.endTime != null) && 
        (this.form.regularDays != null))
    },
    step5Valid() {
      return this.form.subject != null && this.form.regular != null
    },
    step6Valid() {
      return this.form.subject != null && this.form.regular != null
    },
    originSelected: function(address) {
      this.form.origin = address;
    },
    destinationSelected: function(address) {
      this.form.destination = address;
    },
    changePunctualStartDate() {
      this.form.punctualStartDate = moment(this.punctualStartDate);
    },
    clearPunctualStartDate() {
      this.punctualStartDate = null;
      this.form.punctualStartDate = null;
    },
    changeStartTime() {
      this.form.startTime = this.startTime;
    },
    clearStartTime() {
      this.startTime = null;
      this.form.startTime = null;
    },
    changeEndTime() {
      this.form.endTime = this.endTime;
    },
    clearEndTime() {
      this.endTime = null;
      this.form.endTime = null;
    },
    save (date) {
      this.$refs.menu.save(date);
      this.$refs.picker.activePicker = 'YEAR';
      this.menu = false;
    },
    // // should be get all structures
    // getStructures() {
    //   axios.post(this.$t("structures.route")) //TODO : find route list of strauctures by territory
    //     .then(res => {
    //       this.structures = res.data; 
    //     });
    // },
    resetAlert() {
      this.alert = {
        type: "success",
        message: "",
        show: false
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.v-stepper{
  box-shadow:none;
  .v-stepper__step{
      padding-top:5px;
      padding-bottom:5px;
    .v-stepper__label{
      span{
        text-shadow:none !important;
      }
    }
  }
}
</style>