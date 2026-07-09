import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp();

export { app };

import VehicleDatabaseController from './controllers/vehicle_database_controller.js';
import StageWizardController from './controllers/stage_wizard_controller.js';
import WatermarkController from './controllers/watermark_controller.js';
import ImageAnnotatorController from './controllers/image_annotator_controller.js';

app.register('vehicle-database', VehicleDatabaseController);
app.register('stage-wizard', StageWizardController);
app.register('watermark', WatermarkController);
app.register('image-annotator', ImageAnnotatorController);
