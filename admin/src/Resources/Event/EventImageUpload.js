import React, { useEffect } from 'react';
import { FormDataConsumer } from 'react-admin';
import { useForm } from 'react-final-form';

import ImageUpload from '../../components/media/ImageUpload';

const EventImageUpload = ({ label }) => {
  const form = useForm();

  return (
    <FormDataConsumer>
      {(formDataProps) => {
        return (
          <ImageUpload
            imageId={
              formDataProps.formData.images &&
              formDataProps.formData.images[0] &&
              formDataProps.formData.images[0].id
            }
            onChange={(image) => {
              image.id && form.change('images', ['/images/' + image.id]);
            }}
            referenceField="event"
            referenceId={formDataProps.formData.originId}
            label={label}
          />
        );
      }}
    </FormDataConsumer>
  );
};

export default EventImageUpload;
