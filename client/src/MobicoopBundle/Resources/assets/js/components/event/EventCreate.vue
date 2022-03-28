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
      <v-form
        ref="form"
        v-model="valid"
      >
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
                  :rules="(mandatoryDescription) ? descriptionRules : null"
                  :label="(mandatoryDescription) ? $t('form.description.label')+''+$t('form.mandatoryCharacter') : $t('form.description.label')"
                  counter="512"
                />
              </v-col>
            </v-row>
            <v-row justify="center">
              <v-col cols="6">
                <v-textarea
                  v-model="fullDescription"
                  :rules="(mandatoryFullDescription) ? fullDescriptionRules : null"
                  :label="(mandatoryFullDescription) ? $t('form.fullDescription.label')+''+$t('form.mandatoryCharacter') : $t('form.fullDescription.label')"
                  auto-grow
                  clearable
                  outlined
                  counter="2500"
                  row-height="24"
                />
              </v-col>
            </v-row>
            <v-row justify="center">
              <v-col cols="6">
                <geocomplete
                  :uri="geoSearchUrl"
                  :results-order="geoCompleteResultsOrder"
                  :palette="geoCompletePalette"
                  :chip="geoCompleteChip"
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
                  ref="avatar"
                  v-model="avatar"
                  :rules="(mandatoryFullDescription) ? avatarRules : null"
                  accept="image/png, image/jpeg, image/jpg"
                  :label="(mandatoryFullDescription) ? $t('form.avatar.label')+' '+$t('form.mandatoryCharacter') : $t('form.avatar.label')"
                  prepend-icon="mdi-image"
                  :hint="$t('form.avatar.minPxSize', {size: imageMinPxSize})+', '+$t('form.avatar.maxMbSize', {size: imageMaxMbSize})"
                  persistent-hint
                  show-size
                  @change="selectedAvatar"
                />
              </v-col>
            </v-row>
            <v-row justify="center">
              <v-col cols="6">
                <div class="text-center">
                  <v-btn
                    rounded
                    color="secondary"
                    :loading="loading"
                    :disabled="!valid"
                    @click="dialog=true"
                  >
                    {{ $t('buttons.create.label') }}
                  </v-btn>
                </div>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-form>
    </v-container>
    <v-dialog
      v-model="dialog"
      width="550"
    >
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
  </v-main>
</template>
<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/event/EventCreate/";
import Geocomplete from "@components/utilities/geography/Geocomplete";
import moment from "moment";
import maxios from "@utils/maxios";

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
    Geocomplete
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
    imageMinPxSize: {
      type: Number,
      default: null
    },
    imageMaxMbSize: {
      type: Number,
      default: null
    },
    mandatoryDescription: {
      type: Boolean,
      default: true
    },
    mandatoryFullDescription: {
      type: Boolean,
      default: true
    },
    mandatoryImage: {
      type: Boolean,
      default: true
    },
    geoCompleteResultsOrder: {
      type: Array,
      default: null
    },
    geoCompletePalette: {
      type: Object,
      default: () => ({})
    },
    geoCompleteChip: {
      type: Boolean,
      default: false
    },
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
      locale: localStorage.getItem("X-LOCALE"),
      avatarRules: [
        v => !!v || this.$t("form.avatar.required"),
        v => !v || v.size < this.imageMaxMbSize*1024*1024 || this.$t("form.avatar.mbSize", { size: this.imageMaxMbSize }),
        v => !v || this.avatarHeight >= this.imageMinPxSize || this.$t("form.avatar.pxSize", { size: this.imageMinPxSize, height: this.avatarHeight, width: this.avatarWidth }),
        v => !v || this.avatarWidth >= this.imageMinPxSize || this.$t("form.avatar.pxSize", { size: this.imageMinPxSize, height: this.avatarHeight, width: this.avatarWidth }),
      ],
      eventAddress: null,
      name: null,
      nameRules: [
        v => !!v || this.$t("form.name.required"),
      ],
      description: null,
      descriptionRules: [
        v => !!v || this.$t("form.description.required"),
        v => (v||'').length <= 512 || this.$t("error.event.descriptionLength"),
      ],
      fullDescription: null,
      fullDescriptionRules: [
        v => !!v || this.$t("form.fullDescription.required"),
        v => (v||'').length <= 2500 || this.$t("error.event.fullDescriptionLength"),

      ],
      startDateRules: [
        v => !!v || this.$t("startDate.error"),
      ],
      endDateRules: [
        v => !!v || this.$t("endDate.error"),
      ],
      isPrivate: false,
      avatar: null,
      avatarHeight: null,
      avatarWidth: null,
      loading: false,
      snackError: null,
      snackbar: false,
      urlEvent: null,
      urlEventRules: [
        v => !v || /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([-.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/.test(v) || this.$t("form.urlEvent.error")
      ],
      dialog: false,
      endDatePickerMinDate: null,
      startDatePickerMaxDate: null,
      nowDate : new Date().toISOString().slice(0,10),
      valid: false
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
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    addressSelected: function(address) {
      this.eventAddress = address;
    },
    createEvent() {
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

      maxios
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
    },
    updateEndDatePickerMinDate () {
      // add one day because otherwise we get one day before the actual date
      this.endDatePickerMinDate = moment(this.startDate).add(1, 'd').toISOString();
    },
    updateStartDatePickerMaxDate () {
      // add one day because otherwise we get one day before the actual date
      this.startDatePickerMaxDate = moment(this.endDate).add(1, 'd').toISOString();
    },
    selectedAvatar() {
      this.avatarWidth = null;
      this.avatarHeight = null;

      if (!this.avatar) return;
      let reader = new FileReader();

      reader.readAsDataURL(this.avatar);
      reader.onload = evt => {
        let self = this;
        let img = new Image();
        img.onload = () => {
          self.avatarHeight = img.height;
          self.avatarWidth = img.width;
          self.$refs.avatar.validate()
        }
        img.src = evt.target.result;
      }

    }
  }
}
</script>

<style>

</style>
