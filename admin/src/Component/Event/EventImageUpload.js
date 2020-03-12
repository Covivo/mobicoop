import React from 'react';

import { FormDataConsumer, } from 'react-admin';

import { useForm } from 'react-final-form';

import ImageUpload from '../Utilities/ImageUpload';

const EventImageUpload = (props) => {
    const form      = useForm()

    return (
        <FormDataConsumer >
            {formDataProps => {
                return <ImageUpload 
                            imageId={formDataProps.formData.images && formDataProps.formData.images[0] } 
                            onChange={image=>image.id && form.change('images', ["/images/"+image.id])} 
                            referenceField="event" 
                            referenceId={formDataProps.formData.originId} 
                        />
                }
            }
        </FormDataConsumer>
    )
}

export default EventImageUpload
