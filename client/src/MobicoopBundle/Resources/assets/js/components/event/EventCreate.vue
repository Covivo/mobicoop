<template>
  <v-main>
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
              <v-text-field
                v-model="description"
                :rules="descriptionRules"
                :label="$t('form.description.label')"
                counter="512"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="6">
              <v-textarea
                v-model="fullDescription"
                :rules="fullDescriptionRules"
                :label="$t('form.fullDescription.label')"
                auto-grow
                clearable
                outlined
                counter
                row-height="24"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="6">
              <GeoComplete 
                :url="geoSearchUrl"
                :token="user ? user.token : ''"
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
                min-width="290px"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    :value="computedOutwardDateFormat"
                    :rules="startDateRules"
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
                  v-model="startDate"
                  :locale="locale"
                  no-title
                  first-day-of-week="1"
                  :min="nowDate"
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
                min-width="290px"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    :value="computedReturnDateFormat"
                    :rules="endDateRules"
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
                  v-model="endDate"
                  :locale="locale"
                  no-title
                  first-day-of-week="1"
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
                ref="menuStartTime"
                v-model="menuStartTime"
                :close-on-content-click="false"
                :return-value.sync="startTime"
                transition="scale-transition"
                offset-y
                max-width="290px"
                min-width="290px"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    v-model="startTime"
                    :label="$t('startTime.label')"
                    prepend-icon=""
                    readonly
                    v-on="on"
                  />
                </template>
                <v-time-picker
                  v-if="menuStartTime"
                  v-model="startTime"
                  format="24hr"
                  header-color="secondary"
                  @click:minute="$refs.menuStartTime.save(startTime)"
                />
              </v-menu>
            </v-col>

            <v-col
              cols="3"
            >
              <v-menu
                ref="menuEndTime"
                v-model="menuEndTime"
                :close-on-content-click="false"
                :return-value.sync="endTime"
                transition="scale-transition"
                offset-y
                max-width="290px"
                min-width="290px"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    v-model="endTime"
                    :label="$t('endTime.label')"
                    prepend-icon=""
                    readonly
                    v-on="on"
                  />
                </template>
                <v-time-picker
                  v-if="menuEndTime"
                  v-model="endTime"
                  format="24hr"
                  header-color="secondary"
                  @click:minute="$refs.menuEndTime.save(endTime)"
                />
              </v-menu>
            </v-col>
          </v-row>
          <!-- END times -->

          <!-- URL -->
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

          <!-- Private ? -->
          <v-row justify="center">
            <v-col cols="6">
              <v-switch
                v-model="isPrivate"
                color="success"
                inset
              >
                <template v-slot:label>
                  {{ $t('form.private.label') }} 
                  <v-tooltip
                    color="info"
                    right
                  >
                    <template v-slot:activator="{ on }">
                      <v-icon v-on="on">
                        mdi-help-circle-outline
                      </v-icon>
                    </template>
                    <span>{{ $t('form.private.tooltip') }}</span>
                  </v-tooltip>
                </template>
              </v-switch>
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
                  width="550"
                >
                  <template v-slot:activator="{ on }">
                    <v-btn
                      rounded
                      color="secondary"
                      :loading="loading"
                      v-on="on"
                    >
                      {{ $t('buttons.create.label') }}
                    </v-btn>
                  </template>
                  <v-card>
                    <v-card-title
                      class="text-h5 grey lighten-2"
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
                        color="secondary"
                        text
                        @click="createEvent"
                      >
                        {{ $t('popUp.validation') }}
                      </v-btn>
                      <v-btn
                        color="secondary"
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
  </v-main>
</template>
<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/event/EventCreate/";
import GeoComplete from "@components/utilities/GeoComplete";
import moment from "moment";
import axios from "axios";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
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
      startDate: null,
      endDate : null,
      startTime: null,
      endTime: null,
      menuOutwardDate: false,
      menuReturnDate: false,
      menuStartTime: false,
      menuEndTime: false,
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
      description: null,
      descriptionRules: [
        v => !!v || this.$t("form.description.required"),
        v => (v||'').length <= 512 || this.$t("error.event.length"),
      ],
      fullDescription: null,
      fullDescriptionRules: [
        v => !!v || this.$t("form.fullDescription.required"),
      ],
      startDateRules: [
        v => !!v || this.$t("startDate.error"),
      ],
      endDateRules: [
        v => !!v || this.$t("endDate.error"),
      ],
      isPrivate: false,
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
      nowDate : new Date().toISOString().slice(0,10)
    }
  },
  computed :{
    computedOutwardDateFormat() {
      return this.startDate
        ? moment(this.startDate).format(this.$t("fullDate"))
        : "";
    },
    computedReturnDateFormat() {
      return this.endDate
        ? moment(this.endDate).format(this.$t("fullDate"))
        : "";
    },
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    addressSelected: function(address) {
      this.eventAddress = address;
    },
    createEvent() {
      this.dialog = false;
      this.loading = true;
      if (this.name  && this.description && this.fullDescription && this.avatar && this.eventAddress && this.startDate && this.endDate) {
        let newEvent = new FormData();
        newEvent.append("name", this.name);
        newEvent.append("fullDescription", this.fullDescription);
        newEvent.append("description", this.description);
        newEvent.append("private", this.isPrivate);
        newEvent.append("avatar", this.avatar);
        newEvent.append("address", JSON.stringify(this.eventAddress));
        newEvent.append("startDate", this.startDate);
        newEvent.append("endDate", this.endDate);
        if (this.startTime) newEvent.append("startTime", this.startTime);
        if (this.endTime) newEvent.append("endTime", this.endTime);
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
      this.endDatePickerMinDate = moment(this.startDate).add(1, 'd').toISOString();
    },
    updateStartDatePickerMaxDate () {
      // add one day because otherwise we get one day before the actual date
      this.startDatePickerMaxDate = moment(this.endDate).add(1, 'd').toISOString();
    }
  }
}
</script>

<style>

</style>