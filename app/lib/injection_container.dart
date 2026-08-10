import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:get_it/get_it.dart';
import 'core/network/api_interceptor.dart';
import 'core/network/dio_client.dart';
import 'core/settings/app_settings_cubit.dart';
import 'features/auth/presentation/bloc/auth_bloc.dart';
import 'features/auth/domain/repositories/auth_repository.dart';
import 'features/auth/data/repositories/auth_repository_impl.dart';
import 'features/auth/data/sources/auth_remote_data_source.dart';
import 'features/store/presentation/bloc/store_bloc.dart';
import 'features/store/presentation/bloc/cart_bloc.dart';
import 'features/store/presentation/cubit/orders_cubit.dart';
import 'features/store/domain/repositories/store_repository.dart';
import 'features/store/data/repositories/store_repository_impl.dart';
import 'features/store/data/sources/store_remote_data_source.dart';
import 'features/clinical/presentation/bloc/clinical_bloc.dart';
import 'features/clinical/domain/repositories/clinical_repository.dart';
import 'features/clinical/data/repositories/clinical_repository_impl.dart';
import 'features/clinical/data/sources/clinical_remote_data_source.dart';
import 'features/clinical/presentation/cubit/appointment_availability_cubit.dart';
import 'features/communication/presentation/bloc/communication_bloc.dart';
import 'features/communication/domain/repositories/communication_repository.dart';
import 'features/communication/data/repositories/communication_repository_impl.dart';
import 'features/communication/data/sources/communication_remote_data_source.dart';
import 'features/notifications/data/sources/notification_remote_data_source.dart';
import 'features/notifications/presentation/bloc/notification_bloc.dart';

final sl = GetIt.instance;

Future<void> init() async {
  // Core
  sl.registerLazySingleton(() => const FlutterSecureStorage());
  sl.registerLazySingleton(() => Dio());
  sl.registerLazySingleton(() => ApiInterceptor(sl()));
  sl.registerLazySingleton(() => DioClient(sl(), sl()));
  sl.registerLazySingleton(() => AppSettingsCubit(sl(), sl()));

  // Features - Auth
  sl.registerLazySingleton(() => AuthRemoteDataSource(sl()));
  sl.registerLazySingleton<AuthRepository>(
    () => AuthRepositoryImpl(sl(), sl(), sl()),
  );
  sl.registerFactory(() => AuthBloc(sl()));

  // Features - Store
  sl.registerLazySingleton<StoreRemoteDataSource>(
    () => StoreRemoteDataSourceImpl(sl()),
  );
  sl.registerLazySingleton<StoreRepository>(() => StoreRepositoryImpl(sl()));
  sl.registerFactory(() => StoreBloc(sl()));
  sl.registerFactory(() => OrdersCubit(sl()));
  sl.registerLazySingleton(() => CartBloc());

  // Features - Clinical
  sl.registerLazySingleton<ClinicalRemoteDataSource>(
    () => ClinicalRemoteDataSourceImpl(sl()),
  );
  sl.registerLazySingleton<ClinicalRepository>(
    () => ClinicalRepositoryImpl(sl()),
  );
  sl.registerFactory(() => ClinicalBloc(sl()));
  sl.registerFactory(() => AppointmentAvailabilityCubit(sl()));

  // Features - Communication
  sl.registerLazySingleton<CommunicationRemoteDataSource>(
    () => CommunicationRemoteDataSourceImpl(sl(), sl()),
  );
  sl.registerLazySingleton<CommunicationRepository>(
    () => CommunicationRepositoryImpl(sl()),
  );
  sl.registerFactory(() => CommunicationBloc(sl()));

  // Features - Notifications
  sl.registerLazySingleton<NotificationRemoteDataSource>(
    () => NotificationRemoteDataSourceImpl(sl()),
  );
  sl.registerFactory(() => NotificationBloc(sl()));
}
