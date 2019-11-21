<template>
  <v-content>
    <!--SnackBar-->
    <v-snackbar
      v-model="snackbar"
      color="error"
      top
    >
      {{ snackError }}
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>
    <v-container>
      <v-row 
        justify="center"
      >
        <v-col
          cols="12"
          md="8"
          xl="6"
          align="center"
        >
          <h1>{{ $t('title') }}</h1>
        </v-col>
      </v-row>
      <v-row
        justify="center"
        align="center"
      >
        <v-col
          cols="12"
          align="center"
        >
          <v-row justify="center">
            <v-col cols="6">
              <v-text-field
                v-model="name"
                :rules="nameRules"
                :label="$t('form.name.label')"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="6">
              <v-textarea
                v-model="fullDescription"
                :rules="fullDescriptionRules"
                :label="$t('form.fullDescription.label')"
                rows="5"
                auto-grow
                clearable
                outlined
                row-height="24"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="6">
              <GeoComplete 
                :url="geoSearchUrl"
                :label="$t('form.address.label')"
                @address-selected="addressSelected"
              />
            </v-col>
          </v-row>

          <!-- START picker -->
          <v-row
            align="center"
            justify="center"
            dense
          >
            <!-- Outward date -->
            <v-col
              cols="3"
            >
              <v-menu
                v-model="menuOutwardDate"
                :close-on-content-click="false"
                transition="scale-transition"
                offset-y
                full-width
                min-width="290px"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    :value="computedOutwardDateFormat"
                    :label="$t('startDate.label')"
                    readonly
                    clearable
                    v-on="on"
                  >
                    <v-icon
                      slot="prepend"
                    >
                      mdi-arrow-right-circle-outline
                    </v-icon>
                  </v-text-field>
                </template>
                <v-date-picker
                  v-model="outwardDate"
                  :locale="locale"
                  no-title
                  :max="startDatePickerMaxDate"
                  @input="menuOutwardDate = false"
                  @change="updateEndDatePickerMinDate()"
                />
              </v-menu>
            </v-col>

            <v-col
              cols="3"
            >
              <v-menu
                v-model="menuReturnDate"
                :close-on-content-click="false"
                transition="scale-transition"
                offset-y
                full-width
                min-width="290px"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    :value="computedReturnDateFormat"
                    :label="$t('endDate.label')"
                    prepend-icon=""
                    readonly
                    v-on="on"
                  >
                    <v-icon
                      slot="prepend"
                    >
                      mdi-arrow-left-circle-outline
                    </v-icon>
                  </v-text-field>
                </template>
                <v-date-picker
                  v-model="returnDate"
                  :locale="locale"
                  no-title
                  :min="endDatePickerMinDate"
                  @input="menuReturnDate = false"
                  @change="updateStartDatePickerMaxDate()"
                />
              </v-menu>
            </v-col>
          </v-row>

          <!-- END date picker -->

          <!-- START times -->
          <v-row
            align="center"
            justify="center"
            dense
          >
            <v-col
              cols="3"
            >
              <v-menu
                ref="menuOutwardTime"
                v-model="menuOutwardTime"
                :close-on-content-click="false"
                :return-value.sync="outwardTime"
                transition="scale-transition"
                offset-y
                full-width
                max-width="290px"
                min-width="290px"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    v-model="outwardTime"
                    :label="$t('outwardTime.label')"
                    prepend-icon=""
                    readonly
                    v-on="on"
                  />
                </template>
                <v-time-picker
                  v-if="menuOutwardTime"
                  v-model="outwardTime"
                  format="24hr"
                  :max="startDatePickerMaxTime"
                  header-color="secondary"
                  @click:minute="$refs.menuOutwardTime.save(outwardTime)"
                  @change="updateEndTimePickerMinTime()"
                />
              </v-menu>
            </v-col>

            <v-col
              cols="3"
            >
              <v-menu
                ref="menuReturnTime"
                v-model="menuReturnTime"
                :close-on-content-click="false"
                :return-value.sync="returnTime"
                transition="scale-transition"
                offset-y
                full-width
                max-width="290px"
                min-width="290px"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    v-model="returnTime"
                    :label="$t('returnTime.label')"
                    prepend-icon=""
                    readonly
                    v-on="on"
                  />
                </template>
                <v-time-picker
                  v-if="menuReturnTime"
                  v-model="returnTime"
                  format="24hr"
                  :min="endDatePickerMinTime"
                  header-color="secondary"
                  @click:minute="$refs.menuReturnTime.save(returnTime)"
                  @change="updateStartTimePickerMaxTime()"
                />
              </v-menu>
            </v-col>
          </v-row>
          <!-- END times -->




          <v-row justify="center">
            <v-col cols="6">
              <v-tooltip
                left
                color="info"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    v-model="urlEvent"
                    :rules="urlEventRules"
                    :label="$t('form.urlEvent.label')"
                    v-on="on"
                  />
                </template>
                <span>{{ $t('form.urlEvent.tooltips') }}</span>
              </v-tooltip>
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="6">
              <v-file-input
                v-model="avatar"
                :rules="avatarRules"
                accept="image/png, image/jpeg, image/bmp"
                :label="$t('form.avatar.label')"
                prepend-icon="mdi-image"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="6">
              <div class="text-center">
                <v-dialog
                  v-model="dialog"
                  width="500"
                >
                  <template v-slot:activator="{ on }">
                    <v-btn
                      rounded
                      color="primary"
                      :loading="loading"
                      v-on="on"
                    >
                      {{ $t('buttons.create.label') }}
                    </v-btn>
                  </template>
                  <v-card>
                    <v-card-title
                      class="headline grey lighten-2"
                      primary-title
                    >
                      {{ $t('popUp.title') }}
                    </v-card-title>

                    <v-card-text>
                      {{ $t('popUp.label') }}
                    </v-card-text>

                    <v-divider />

                    <v-card-actions>
                      <v-spacer />
                      <v-btn
                        color="primary"
                        text
                        @click="createEvent"
                      >
                        {{ $t('popUp.validation') }}
                      </v-btn>
                      <v-btn
                        color="primary"
                        text
                        @click="dialog = false"
                      >
                        {{ $t('popUp.cancel') }}
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
              </div>
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>
<script>

import { merge } from "lodash";
import Translations from "@translations/components/event/EventCreate.json";
import TranslationsClient from "@clientTranslations/components/event/EventCreate.json";
import GeoComplete from "@components/utilities/GeoComplete";
import moment from "moment";
import axios from "axios";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
  },
  components: {
    GeoComplete
  },
  props:{
    user: {
      type: Object,
      default: null
    },
    community: {
      type: Array,
      default: null
    },
    geoSearchUrl: {
      type: String,
      default: null
    },
    avatarSize: {
      type: String,
      default: null
    }
  },
  data () {
    return {
      outwardDate: null,
      returnDate : null,
      outwardTime: null,
      returnTime: null,
      menuOutwardDate: false,
      menuReturnDate: false,
      menuOutwardTime: false,
      menuReturnTime: false,
      locale: this.$i18n.locale,
      avatarRules: [
        v => !!v || this.$t("form.avatar.required"),
        v => !v || v.size < this.avatarSize || this.$t("form.avatar.size")+" (Max "+(this.avatarSize/1000000)+"MB)"
      ],
      eventAddress: null,
      name: null,
      nameRules: [
        v => !!v || this.$t("form.name.required"),
      ],
      fullDescription: null,
      fullDescriptionRules: [
        v => !!v || this.$t("form.fullDescription.required"),
      ],
      avatar: null,
      loading: false,
      snackError: null,
      snackbar: false,
      urlEvent: null,
      urlEventRules: [
        v => !v || /([\w+-]*\.[\w+]*$)/.test(v) || this.$t("form.urlEvent.error")
      ],
      dialog: false,
      endDatePickerMinDate: null,
      startDatePickerMaxDate: null,
      endDatePickerMinTime: null,
      startDatePickerMaxTime: null
    }
  },
  computed :{
    computedOutwardDateFormat() {
      moment.locale(this.locale);
      return this.outwardDate
        ? moment(this.outwardDate).format(this.$t("ui.i18n.date.format.fullDate"))
        : "";
    },
    computedReturnDateFormat() {
      moment.locale(this.locale);
      return this.returnDate
        ? moment(this.returnDate).format(this.$t("ui.i18n.date.format.fullDate"))
        : "";
    },
  },
  methods: {
    addressSelected: function(address) {
      this.eventAddress = address;
    },
    createEvent() {
      this.loading = true;
      this.dialog = false;
      if (this.name  && this.fullDescription && this.avatar && this.eventAddress) {
        let newEvent = new FormData();
        newEvent.append("name", this.name);
        newEvent.append("fullDescription", this.fullDescription);
        newEvent.append("avatar", this.avatar);
        newEvent.append("address", JSON.stringify(this.eventAddress));
        newEvent.append("outwardDate", this.outwardDate);
        newEvent.append("returnDate", this.returnDate);
        if (this.outwardTime) newEvent.append("outwardTime", this.outwardTime);
        if (this.returnTime) newEvent.append("returnTime", this.returnTime);
        if (this.urlEvent) newEvent.append("urlEvent", this.urlEvent);

        axios 
          .post(this.$t('buttons.create.route'), newEvent, {
            headers:{
              'content-type': 'multipart/form-data'
            }
          })
          .then(res => {
            if (res.data.includes('error')) {
              this.snackError = this.$t(res.data)
              this.snackbar = true;
              this.loading = false;
            }
            else window.location.href = this.$t('redirect.route');
          });
      } else {
        this.snackError = this.$t('error.event.required')
        this.snackbar = true;
        this.loading = false;
      }    
    },
    updateEndDatePickerMinDate () {
      // add one day because otherwise we get one day before the actual date
      this.endDatePickerMinDate = moment(this.outwardDate).add(1, 'd').toISOString();
    },
    updateStartDatePickerMaxDate () {
      // add one day because otherwise we get one day before the actual date
      this.startDatePickerMaxDate = moment(this.returnDate).add(1, 'd').toISOString();
    },
    updateStartTimePickerMaxTime () {
      this.startDatePickerMaxTime = this.returnTime;
    },
    updateEndTimePickerMinTime () {
      this.endDatePickerMinTime = this.outwardTime;
    }
  }
}
</script>

<style>

</style>