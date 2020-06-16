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
        <v-stepper
          v-model="step"
          non-linear
          class="elevation-0"
        >
          <v-stepper-header
            class="elevation-0"
          >
            <v-stepper-step
              :step="1"
              editable
              edit-icon
            >
              {{ $t('stepper.origin') }}
            </v-stepper-step>

            <v-divider />

            <v-stepper-step
              :step="2"
              editable
              edit-icon
            >
              {{ $t('stepper.service') }}
            </v-stepper-step>

            <v-divider />

            <v-stepper-step
              :step="3"
              editable
              edit-icon
            >
              {{ $t('stepper.yourJourney') }}
            </v-stepper-step>

            <v-divider />

            <v-stepper-step 
              :step="4"
              editable
              edit-icon
            >
              {{ $t('stepper.ponctual') }}
            </v-stepper-step>

            <v-divider />

            <v-stepper-step 
              :step="5"
              editable
              edit-icon
            >
              {{ $t('stepper.you') }}
            </v-stepper-step>

            <v-divider />

            <v-stepper-step
              :step="6"
              editable
              edit-icon
            >
              {{ $t('stepper.summary') }}
            </v-stepper-step>
          </v-stepper-header>


          <!-- ORIGIN -->
          <v-stepper-items>
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
                      />
                    </v-col>
                  </v-row>
                </v-card>
                
                <v-btn
                  ref="button"
                  rounded
                  class="my-13 mr-12 mt-12 "
                  color="secondary"
                  width="150px"
                  @click="nextStep(1)"
                >
                  {{ $t('ui.button.next') }}
                </v-btn>
              </v-form>
            </v-stepper-content>

            <!-- SERVICE -->
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
                  <!--Structure and subject-->
                  <v-row
                    justify="center"
                  >
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
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('structure.criteria') }}</span> 
                      </p>
                      <v-switch
                        v-model="form.hasRSA"
                        color="primary"
                        inset
                        :label="$t('structure.hasRSA.placeholder')"
                      />
                      <v-switch
                        v-model="form.city"
                        color="primary"
                        inset
                        :label="$t('structure.city.placeholder')" 
                      />
                      <!-- TODO: "j'habite dans une commune de "structure.name" -->
                    </v-col>

                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('structure.info') }}</span> 
                      </p>
                      <v-text-field
                        :label="$t('structure.territory')"
                      />
                    </v-col>
                    <v-col
                      cols="8"
                    >
                      <div class="text-left">
                        <v-btn
                          ref="button"
                          rounded
                          color="primary"
                          outlined
                          width="325px"    
                        >
                          {{ $t('structure.send') }}
                        </v-btn>
                      </div>
                    </v-col>
                  </v-row>
                </v-card>

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
                <v-btn
                  ref="button"
                  rounded
                  class="mr-4 mb-100 mt-12"
                  color="secondary"
                  width="150px"
                  @click="nextStep(2)"
                >
                  {{ $t('ui.button.next') }}
                </v-btn>
              </v-form>
            </v-stepper-content>

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
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('yourJourney.whatdoyouWantTodo') }}</span> 
                      </p>
                      <v-select
                        v-model="form.subjects"
                        :items="subjects"
                        item-text="name"
                        item-value="id"
                        :label="$t('yourJourney.whatdoyouWantTodo')"
                      />
                    </v-col>

                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('yourJourney.whereShouldWeGo') }}</span> 
                      </p>
                      <!--GeoComplete -->
                      <GeoComplete
                        :url="geoSearchUrl"
                        :label="$t('yourJourney.destination')"
                        :token="user ? user.geoToken : ''"
                        :display-name-in-selected="false"
                      />                  
                    </v-col>
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('yourJourney.regularTrip.question') }}</span> 
                      </p>
                      <v-radio-group
                        v-model="radios"
                        :mandatory="false"
                      >
                        <v-radio
                          :label="$t('yourJourney.regularTrip.no')"
                          value="radio-1"
                        />
                        <v-radio
                          :label="$t('yourJourney.regularTrip.yes')"
                          value="radio-2"
                        />
                      </v-radio-group>                                 
                    </v-col>
                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('yourJourney.otherInfo') }}</span> 
                      </p>

                      <v-select
                        item-text="name"
                        item-value="id"
                        :label="$t('yourJourney.otherInfo')"
                      />
                    </v-col>

                    <v-col
                      cols="8"
                    >
                      <p class="title text-left">
                        <span class="font-weight-black  "> {{ $t('yourJourney.otherDetails') }}</span> 
                      </p>

                      <v-text-field
                        :label="$t('yourJourney.otherDetails')"
                      />  
                    </v-col>
                  </v-row>
                </v-card>

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
                <v-btn
                  ref="button"
                  rounded
                  class="mr-4 mb-100 mt-12"
                  color="secondary"
                  width="150px"

                  @click="nextStep(3)"
                >
                  {{ $t('ui.button.next') }}
                </v-btn>
              </v-form>
            </v-stepper-content>
            <v-stepper-content step="4">
              <v-form
                ref="step 4"
                class="pb-2"
                @submit.prevent
              >
                <v-card
                  class="mb-12"
                  flat
                />

                <v-btn
                  ref="button"
                  rounded
                  class="my-13 mr-12 mt-12 "
                  color="secondary"
                  @click="--step"
                >
                  {{ $t('ui.button.previous') }}
                </v-btn>
                <v-btn
                  ref="button"
                  rounded
                  class="mr-4 mb-100 mt-12"
                  color="secondary"
                  width="150px"

                  @click="nextStep(5)"
                >
                  {{ $t('ui.button.next') }}
                </v-btn>
              </v-form>
            </v-stepper-content>

            <!--USER-->
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
                            v-model="yearOfBirth"
                            :label="$t('yearOfBirth.placeholder') + ' *'"
                            :rules="rules.yearsOfBirthRules"
                            v-on="on"
                          />
                        </template>
                        <v-date-picker
                          ref="picker"
                          v-model="form.yearOfBirth"
                          no-title
                          reactive
                          first-day-of-week="1"
                          :max="years.max"
                          :min="years.min"
                          @input="save"
                        >
                          <v-spacer />
                          <v-btn
                            text
                            color="error"
                            @click="menu = false"
                          >
                            {{ $t('ui.buttons.cancel.label') }}
                          </v-btn>
                          <v-btn
                            text
                            color="secondary"
                            @click="$refs.menu.save(form.yearOfBirth)"
                          >
                            {{ $t('ui.buttons.validate.label') }}
                          </v-btn>
                        </v-date-picker>
                      </v-menu>
                    </v-col>
                  </v-row>
                </v-card>

                <v-btn
                  ref="button"
                  rounded
                  class="my-13 mr-12 mt-12 "
                  color="secondary"
                  @click="--step"
                >
                  {{ $t('ui.button.previous') }}
                </v-btn>
                <v-btn
                  ref="button"
                  rounded
                  class="mr-4 mb-100 mt-12"
                  color="secondary"
                  width="150px"

                  @click="nextStep(6)"
                >
                  {{ $t('ui.button.next') }}
                </v-btn>
              </v-form>
            </v-stepper-content>

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
                <v-btn
                  ref="button"
                  rounded
                  class="my-13 mr-12 mt-12 "
                  color="secondary"
                  @click="--step"
                >
                  {{ $t('ui.button.previous') }}
                </v-btn>
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
    },
    regular: {
      type: Boolean,
      default: false
    },
    structures: {
      type: Array,
      default: null
    },
    subjects: {
      type: Array,
      default: null
    }
  },
  data () {
    return {
      locale: this.$i18n.locale,
      loading: false,
      valid: false,
      alert: {
        type: "success",
        show: false,
        message: ""
      },
      pickerActive: false,

      // stepper
      step: 1,

      form: {      
        address: null,
        structure: null,
        subject: null,
        proof: null,
        comments: null,
        destination: null,
        regular: false,
        need: null,
        givenName: this.user && this.user.givenName ? this.user.givenName : "",
        familyName: this.user && this.user.familyName ? this.user.familyName : "",
        email: this.user && this.user.email ? this.user.email : "",
        phoneNumber: this.user && this.user.telephone ? this.user.telephone : null,
        yearOfBirth: this.user && this.user.birthYear ? moment(this.user.birthYear.toString()).format("YYYY-MM-DD") : null,
        hasRSA: false,
        radios: 'radio-1'
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
        ],
        yearsOfBirthRules: [
          v => !!v || this.$t("yearOfBirth.errors.required"),
        ],
      },
      years: {
        max: moment().format(),
        min: moment().subtract(100, 'years').format()
      }
    }
  },
  computed: {
    yearOfBirth: {
      get () {
        return this.form.yearOfBirth && moment(this.form.yearOfBirth, "YYYY-MM-DD", true).isValid() ? 
          moment(this.form.yearOfBirth).format('YYYY') : null
      },
      set (value) {
        value && moment(value, "YYYY", true).isValid() ?
          this.form.yearOfBirth = moment(value).format("YYYY-MM-DD") : null;
      }
    }
  },
  watch: {
    pickerActive(val) {
      val && this.$nextTick(() => (this.$refs.picker.activePicker = 'YEAR'))
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